<?php

namespace App\Domain\Catalog;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SaveProducerProduct
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $newImages
     */
    public function execute(User $producer, array $data, array $newImages, ?Product $product = null): Product
    {
        $storedImages = [];

        try {
            foreach ($newImages as $index => $image) {
                $extension = match ($image->getMimeType()) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => throw new RuntimeException('Unsupported product image type.'),
                };
                $path = 'products/'.$producer->id.'/'.Str::uuid().'.'.$extension;
                if (! Storage::disk('public')->putFileAs(dirname($path), $image, basename($path))) {
                    throw new RuntimeException('Product image could not be stored.');
                }
                $storedImages[$index] = [
                    'disk' => 'public',
                    'object_path' => $path,
                    'mime_type' => $image->getMimeType(),
                    'size_bytes' => $image->getSize(),
                ];
            }

            return DB::transaction(function () use ($producer, $data, $storedImages, $product): Product {
                $current = $product === null
                    ? new Product(['producer_id' => $producer->id])
                    : Product::query()->whereKey($product->id)->where('producer_id', $producer->id)->lockForUpdate()->firstOrFail();

                if ($product !== null && (int) $current->lock_version !== (int) $data['lock_version']) {
                    abort(409, '商品が更新されています。再読み込みしてからやり直してください。');
                }

                $current->fill([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'delivery_fee_honshu_yen' => $data['delivery_fee_honshu_yen'],
                    'delivery_fee_hokkaido_yen' => $data['delivery_fee_hokkaido_yen'],
                    'delivery_fee_okinawa_yen' => $data['delivery_fee_okinawa_yen'],
                    'publication_state' => $data['publication_state'],
                    'lock_version' => $product === null ? 1 : ((int) $current->lock_version + 1),
                ])->save();

                $variant = $current->variants()->lockForUpdate()->first();
                $variantData = [
                    'option_label' => '通常商品',
                    'is_default' => true,
                    'price_yen' => $data['price_yen'],
                    'stock_quantity' => $data['stock_quantity'],
                    'discount_bps' => (int) round(((float) ($data['discount_percent'] ?? 0)) * 100),
                    'display_order' => 0,
                    'lock_version' => $variant ? ((int) $variant->lock_version + 1) : 1,
                ];
                $variant ? $variant->fill($variantData)->save() : $current->variants()->create($variantData);

                $existing = $current->images()->get()->keyBy(fn ($image) => (string) $image->id);
                $keptIds = [];
                foreach ($data['image_order'] as $order => $token) {
                    [$kind, $identifier] = explode(':', $token, 2);
                    if ($kind === 'existing') {
                        $image = $existing->get($identifier);
                        abort_unless($image !== null, 422, '画像の指定が正しくありません。');
                        $keptIds[] = $identifier;
                        $image->update(['display_order' => 1000 + $order]);
                    } else {
                        abort_unless(isset($storedImages[(int) $identifier]), 422, '画像の指定が正しくありません。');
                        $current->images()->create([...$storedImages[(int) $identifier], 'display_order' => 1000 + $order]);
                    }
                }

                $removedPaths = $existing->except($keptIds)->map(fn ($image) => [$image->disk, $image->object_path])->all();
                $current->images()->whereNotIn('id', $keptIds)->whereIn('id', $existing->keys())->delete();
                foreach ($current->images()->orderBy('display_order')->get() as $order => $image) {
                    $image->update(['display_order' => $order]);
                }
                DB::afterCommit(function () use ($removedPaths): void {
                    foreach ($removedPaths as [$disk, $path]) {
                        Storage::disk($disk)->delete($path);
                    }
                });

                return $current->load(['category:id,name', 'variants', 'images']);
            });
        } catch (\Throwable $exception) {
            foreach ($storedImages as $stored) {
                Storage::disk($stored['disk'])->delete($stored['object_path']);
            }
            throw $exception;
        }
    }
}
