<?php

namespace Database\Seeders;

use App\Enums\ProductPublicationState;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductSampleSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Sample products may only be seeded in local or testing environments.');
        }

        $producer = User::query()
            ->where('role', UserRole::Producer)
            ->whereHas('producerProfile', fn ($query) => $query->whereNotNull('selling_eligible_at'))
            ->orderByRaw('CASE WHEN email = ? THEN 1 ELSE 0 END', ['producer@example.test'])
            ->latest('updated_at')
            ->firstOrFail();

        $fixture = (string) file_get_contents(database_path('fixtures/vegetable-set.svg'));

        DB::transaction(function () use ($producer, $fixture): void {
            $categories = collect(['野菜', '米・穀類', '加工品'])
                ->mapWithKeys(fn (string $name, int $order) => [
                    $name => Category::query()->firstOrCreate(
                        ['name' => $name],
                        ['is_enabled' => true, 'display_order' => $order],
                    ),
                ]);

            $samples = [
                [
                    'name' => '季節の野菜セット', 'category' => '野菜',
                    'description' => '旬の野菜をバランスよく詰め合わせたセットです。',
                    'price_yen' => 2682, 'stock_quantity' => 30, 'discount_bps' => 1000,
                    'publication_state' => ProductPublicationState::Published,
                ],
                [
                    'name' => '高崎トマト', 'category' => '野菜',
                    'description' => '甘みと酸味のバランスが良い、新鮮なトマトです。',
                    'price_yen' => 1280, 'stock_quantity' => 80, 'discount_bps' => 0,
                    'publication_state' => ProductPublicationState::Published,
                ],
                [
                    'name' => '新米 5kg', 'category' => '米・穀類',
                    'description' => '収穫したての香り豊かな新米を5kgでお届けします。',
                    'price_yen' => 3800, 'stock_quantity' => 20, 'discount_bps' => 0,
                    'publication_state' => ProductPublicationState::Published,
                ],
                [
                    'name' => 'ギフトセット', 'category' => '加工品',
                    'description' => '贈り物におすすめの農産物加工品セットです。',
                    'price_yen' => 4500, 'stock_quantity' => 10, 'discount_bps' => 0,
                    'publication_state' => ProductPublicationState::Unpublished,
                ],
            ];

            foreach ($samples as $index => $sample) {
                $product = Product::query()->updateOrCreate(
                    ['producer_id' => $producer->id, 'name' => $sample['name']],
                    [
                        'category_id' => $categories[$sample['category']]->id,
                        'description' => $sample['description'],
                        'type_name' => null,
                        'delivery_fee_honshu_yen' => 500,
                        'delivery_fee_hokkaido_yen' => 900,
                        'delivery_fee_okinawa_yen' => 1100,
                        'publication_state' => $sample['publication_state'],
                        'moderation_state' => 'clear',
                    ],
                );

                ProductVariant::query()->updateOrCreate(
                    ['product_id' => $product->id, 'option_label' => '通常商品'],
                    [
                        'is_default' => true,
                        'price_yen' => $sample['price_yen'],
                        'stock_quantity' => $sample['stock_quantity'],
                        'discount_bps' => $sample['discount_bps'],
                        'display_order' => 0,
                    ],
                );

                if (! $product->images()->exists()) {
                    $path = 'demo/products/'.$product->id.'.svg';
                    Storage::disk('public')->put($path, $fixture);
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'disk' => 'public',
                        'object_path' => $path,
                        'mime_type' => 'image/svg+xml',
                        'size_bytes' => strlen($fixture),
                        'display_order' => 0,
                    ]);
                }
            }
        });
    }
}
