<?php

namespace Database\Seeders;

use App\Enums\ProducerOperationalState;
use App\Enums\ProductPublicationState;
use App\Enums\ScreeningState;
use App\Models\BuyerAddress;
use App\Models\BuyerProfile;
use App\Models\Category;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerProfile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo fixtures may only be seeded in local or testing environments.');
        }

        DB::transaction(function (): void {
            $categories = collect(['野菜', '果物', '米・穀類', '加工品'])
                ->mapWithKeys(fn (string $name, int $order) => [
                    $name => Category::query()->firstOrCreate(
                        ['name' => $name],
                        ['is_enabled' => true, 'display_order' => $order],
                    ),
                ]);

            $buyer = User::query()->firstOrCreate(
                ['email' => 'buyer@example.test'],
                User::factory()->buyer()->make(['email' => 'buyer@example.test'])->getAttributes(),
            );
            BuyerProfile::query()->updateOrCreate(
                ['user_id' => $buyer->id],
                ['name' => '購入者デモ', 'name_phonetic' => 'コウニュウシャデモ', 'phone' => '090-0000-0001'],
            );
            BuyerAddress::query()->updateOrCreate(
                ['buyer_id' => $buyer->id, 'is_default' => true],
                [
                    'recipient_name' => '購入者デモ', 'phone' => '090-0000-0001',
                    'postal_code' => '100-0001', 'prefecture' => '東京都', 'city' => '千代田区',
                    'address_line1' => '千代田1-1',
                ],
            );

            $producer = User::query()->firstOrCreate(
                ['email' => 'producer@example.test'],
                User::factory()->producer()->make(['email' => 'producer@example.test'])->getAttributes(),
            );
            ProducerProfile::query()->updateOrCreate(
                ['user_id' => $producer->id],
                [
                    'farm_name' => 'みのりデモ農園', 'representative_name' => '生産者デモ',
                    'phone' => '090-0000-0002', 'operational_state' => ProducerOperationalState::Active,
                    'selling_eligible_at' => now(),
                ],
            );

            $tenant = PayjpTenant::query()->updateOrCreate(
                ['producer_id' => $producer->id],
                [
                    'provider_tenant_reference' => 'ten_test_minori_demo', 'application_state' => 'approved',
                    'bank_state' => 'registered', 'last_authoritative_sync_at' => now(),
                ],
            );
            foreach (['visa', 'mastercard'] as $brand) {
                PayjpScreening::query()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'card_brand' => $brand],
                    ['state' => ScreeningState::Passed, 'provider_updated_at' => now()],
                );
            }

            $single = Product::query()->updateOrCreate(
                ['producer_id' => $producer->id, 'name' => '高崎トマト'],
                [
                    'category_id' => $categories['野菜']->id, 'description' => 'ローカル開発用の商品データです。',
                    'publication_state' => ProductPublicationState::Published, 'moderation_state' => 'clear',
                ],
            );
            ProductVariant::query()->updateOrCreate(
                ['product_id' => $single->id, 'option_label' => '通常商品'],
                ['is_default' => true, 'price_yen' => 1200, 'stock_quantity' => 30, 'discount_bps' => 0, 'display_order' => 0],
            );

            $typed = Product::query()->updateOrCreate(
                ['producer_id' => $producer->id, 'name' => '季節の野菜セット'],
                [
                    'category_id' => $categories['野菜']->id, 'description' => '種類ごとの価格・在庫・割引確認用データです。',
                    'type_name' => 'セット', 'publication_state' => ProductPublicationState::Published, 'moderation_state' => 'clear',
                ],
            );
            ProductVariant::query()->updateOrCreate(
                ['product_id' => $typed->id, 'option_label' => '通常セット'],
                ['is_default' => false, 'price_yen' => 2980, 'stock_quantity' => 20, 'discount_bps' => 1000, 'display_order' => 0],
            );
            ProductVariant::query()->updateOrCreate(
                ['product_id' => $typed->id, 'option_label' => '大容量セット'],
                ['is_default' => false, 'price_yen' => 4500, 'stock_quantity' => 8, 'discount_bps' => 500, 'display_order' => 1],
            );

            $fixture = (string) file_get_contents(database_path('fixtures/vegetable-set.svg'));
            Storage::disk('public')->put('demo/vegetable-set.svg', $fixture);
            ProductImage::query()->updateOrCreate(
                ['object_path' => 'demo/vegetable-set.svg'],
                [
                    'product_id' => $typed->id, 'disk' => 'public', 'mime_type' => 'image/svg+xml',
                    'size_bytes' => strlen($fixture), 'display_order' => 0,
                ],
            );
        });
    }
}
