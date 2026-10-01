<?php

use App\Enums\ProductPublicationState;
use App\Enums\ProducerOperationalState;
use App\Enums\ScreeningState;
use App\Models\Category;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerProfile;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

function eligibleProducer(array $attributes = []): User
{
    $producer = User::factory()->producer()->create($attributes);
    ProducerProfile::query()->create([
        'user_id' => $producer->id,
        'farm_name' => '山田農園',
        'operational_state' => ProducerOperationalState::Active,
        'selling_eligible_at' => now(),
    ]);
    $tenant = PayjpTenant::query()->create([
        'producer_id' => $producer->id,
        'provider_tenant_reference' => 'ten_'.$producer->id,
        'application_state' => 'approved',
        'bank_state' => 'registered',
    ]);
    foreach (['visa', 'mastercard'] as $brand) {
        PayjpScreening::query()->create([
            'tenant_id' => $tenant->id,
            'card_brand' => $brand,
            'state' => ScreeningState::Passed,
        ]);
    }

    return $producer;
}

it('lists only the authenticated eligible Producers own products', function (): void {
    $producer = eligibleProducer();
    $otherProducer = eligibleProducer();
    $category = Category::factory()->create(['name' => '野菜']);
    $own = Product::factory()->create([
        'producer_id' => $producer->id,
        'category_id' => $category->id,
        'name' => '高崎トマト',
        'publication_state' => ProductPublicationState::Published,
    ]);
    ProductVariant::factory()->create([
        'product_id' => $own->id,
        'price_yen' => 1280,
        'stock_quantity' => 80,
    ]);
    Product::factory()->create(['producer_id' => $otherProducer->id, 'name' => '他の農園の商品']);

    $this->actingAs($producer);

    $this->getJson('/api/v1/producer/products')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $own->id)
        ->assertJsonPath('data.0.name', '高崎トマト')
        ->assertJsonPath('data.0.category', '野菜')
        ->assertJsonPath('data.0.price_yen', 1280)
        ->assertJsonPath('data.0.stock_quantity', 80)
        ->assertJsonPath('data.0.publication_state', 'published')
        ->assertJsonPath('meta.categories.0', '野菜');
});

it('filters Producer products by keyword category publication and stock state', function (): void {
    $producer = eligibleProducer();
    $vegetable = Category::factory()->create(['name' => '野菜']);
    $set = Category::factory()->create(['name' => 'セット']);
    $tomato = Product::factory()->create([
        'producer_id' => $producer->id,
        'category_id' => $vegetable->id,
        'name' => '高崎トマト',
        'publication_state' => ProductPublicationState::Published,
    ]);
    ProductVariant::factory()->create(['product_id' => $tomato->id, 'stock_quantity' => 8]);
    $gift = Product::factory()->create([
        'producer_id' => $producer->id,
        'category_id' => $set->id,
        'name' => 'ギフトセット',
        'publication_state' => ProductPublicationState::Unpublished,
    ]);
    ProductVariant::factory()->create(['product_id' => $gift->id, 'stock_quantity' => 0]);

    $this->actingAs($producer);

    $this->getJson('/api/v1/producer/products?keyword=トマト&category='.urlencode('野菜').'&publication_state=published&stock_state=low_stock')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $tomato->id);

    $this->getJson('/api/v1/producer/products?keyword='.urlencode('P-'.strtoupper(substr($tomato->id, -6))))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $tomato->id);

    $this->getJson('/api/v1/producer/products?stock_state=out_of_stock')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $gift->id);
});
