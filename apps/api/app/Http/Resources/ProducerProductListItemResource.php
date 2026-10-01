<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MissingValue;

/** @mixin Product */
class ProducerProductListItemResource extends JsonResource
{
    /** @return array{id:string,display_id:string,name:string,category:?string,price_yen:?int,has_multiple_prices:bool,stock_quantity:int,discount_bps:int,publication_state:'draft'|'published'|'unpublished',image_url:?string,updated_at:?string} */
    public function toArray(Request $request): array
    {
        $variants = $this->whenLoaded('variants');
        $variantCollection = collect($variants instanceof MissingValue ? [] : $variants);
        $prices = $variantCollection->pluck('price_yen')->filter(static fn ($price): bool => is_numeric($price))->values();
        $stock = (int) $variantCollection->sum('stock_quantity');
        $maxDiscountBps = (int) $variantCollection->max('discount_bps');
        $image = $this->whenLoaded('images');
        $firstImage = collect($image instanceof MissingValue ? [] : $image)->first();

        return [
            'id' => (string) $this->id,
            'display_id' => 'P-'.strtoupper(substr((string) $this->id, -6)),
            'name' => (string) $this->name,
            'category' => $this->category?->name,
            'price_yen' => $prices->isEmpty() ? null : (int) $prices->min(),
            'has_multiple_prices' => $prices->unique()->count() > 1,
            'stock_quantity' => $stock,
            'discount_bps' => $maxDiscountBps,
            'publication_state' => $this->publication_state->value,
            'image_url' => $firstImage instanceof ProductImage ? $this->imageUrl($firstImage) : null,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function imageUrl(ProductImage $image): ?string
    {
        if (! in_array($image->disk, ['public', 's3'], true)) {
            return null;
        }

        return Storage::disk($image->disk)->url($image->object_path);
    }
}
