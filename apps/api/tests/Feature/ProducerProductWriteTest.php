<?php

use App\Enums\ProducerOperationalState;
use App\Enums\ScreeningState;
use App\Models\Category;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function productWriteEligibleProducer(): User
{
    $producer = User::factory()->producer()->create();
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

function validProductPayload(Category $category): array
{
    return [
        'name' => '高崎トマト',
        'category_id' => $category->id,
        'description' => '甘みの強いトマトです。',
        'price_yen' => 1280,
        'stock_quantity' => 80,
        'discount_percent' => 10,
        'delivery_fee_honshu_yen' => 500,
        'delivery_fee_hokkaido_yen' => 900,
        'delivery_fee_okinawa_yen' => 1100,
        'publication_state' => 'published',
        'image_order' => ['new:0'],
        'new_images' => [UploadedFile::fake()->image('tomato.jpg', 800, 800)],
    ];
}

it('creates a complete owned product with one internal default option and an image', function (): void {
    Storage::fake('public');
    $producer = productWriteEligibleProducer();
    $category = Category::factory()->create(['is_enabled' => true]);

    $response = $this->actingAs($producer)->post('/api/v1/producer/products', validProductPayload($category));

    $response->assertCreated()
        ->assertJsonPath('data.name', '高崎トマト')
        ->assertJsonPath('data.price_yen', 1280)
        ->assertJsonPath('data.discount_bps', 1000)
        ->assertJsonPath('data.delivery_fee_hokkaido_yen', 900)
        ->assertJsonCount(1, 'data.images');

    $product = Product::query()->with(['variants', 'images'])->sole();
    expect($product->producer_id)->toBe($producer->id)
        ->and($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->is_default)->toBeTrue()
        ->and($product->images)->toHaveCount(1);
    Storage::disk('public')->assertExists($product->images->first()->object_path);
});

it('updates only an owned product and rejects a stale lock version', function (): void {
    Storage::fake('public');
    $producer = productWriteEligibleProducer();
    $otherProducer = productWriteEligibleProducer();
    $category = Category::factory()->create(['is_enabled' => true]);

    $created = $this->actingAs($producer)
        ->post('/api/v1/producer/products', validProductPayload($category))
        ->assertCreated()
        ->json('data');

    $payload = validProductPayload($category);
    $payload['name'] = '更新トマト';
    $payload['lock_version'] = $created['lock_version'];
    $payload['image_order'] = ['existing:'.$created['images'][0]['id']];
    unset($payload['new_images']);

    $this->actingAs($producer)
        ->post('/api/v1/producer/products/'.$created['id'], $payload)
        ->assertOk()
        ->assertJsonPath('data.name', '更新トマト');

    $payload['name'] = '古い更新';
    $this->actingAs($producer)
        ->post('/api/v1/producer/products/'.$created['id'], $payload)
        ->assertConflict();

    $this->actingAs($otherProducer)
        ->getJson('/api/v1/producer/products/'.$created['id'])
        ->assertNotFound();
});

it('rejects disabled categories negative values and a missing product image', function (): void {
    $producer = productWriteEligibleProducer();
    $category = Category::factory()->create(['is_enabled' => false]);
    $payload = validProductPayload($category);
    $payload['price_yen'] = -1;
    $payload['image_order'] = [];
    unset($payload['new_images']);

    $this->actingAs($producer)
        ->post('/api/v1/producer/products', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id', 'price_yen', 'image_order']);
});
