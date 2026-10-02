<?php

use App\Enums\ProductPublicationState;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

function publishedVariant(int $stock = 5): ProductVariant
{
    $product = Product::factory()->create([
        'publication_state' => ProductPublicationState::Published,
    ]);

    return ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock_quantity' => $stock,
    ]);
}

it('adds a published variant to the authenticated Buyers cart', function (): void {
    $buyer = User::factory()->buyer()->create();
    $variant = publishedVariant();
    $variant->product()->update([
        'delivery_fee_honshu_yen' => 500,
        'delivery_fee_hokkaido_yen' => 900,
        'delivery_fee_okinawa_yen' => 1100,
    ]);

    $this->actingAs($buyer)
        ->postJson('/api/v1/buyer/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('data.items.0.variant_id', $variant->id)
        ->assertJsonPath('data.items.0.quantity', 2)
        ->assertJsonPath('data.items.0.delivery_fee_hokkaido_yen', 900);

    expect(Cart::query()->where('buyer_id', $buyer->id)->count())->toBe(1);
});

it('rejects unavailable cart quantities and prevents cross-Buyer changes', function (): void {
    $buyer = User::factory()->buyer()->create();
    $otherBuyer = User::factory()->buyer()->create();
    $variant = publishedVariant(1);
    $cart = Cart::query()->create(['buyer_id' => $buyer->id, 'state' => 'active']);
    $item = CartItem::query()->create([
        'cart_id' => $cart->id,
        'variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $this->actingAs($buyer)
        ->patchJson("/api/v1/buyer/cart/items/{$item->id}", ['quantity' => 2])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('quantity');

    $this->actingAs($otherBuyer)
        ->deleteJson("/api/v1/buyer/cart/items/{$item->id}")
        ->assertNotFound();

    expect(CartItem::query()->find($item->id))->not->toBeNull();
});

it('exposes only published products to the Buyer catalogue', function (): void {
    $published = Product::factory()->create([
        'publication_state' => ProductPublicationState::Published,
    ]);
    ProductVariant::factory()->create(['product_id' => $published->id]);
    Product::factory()->create(['publication_state' => ProductPublicationState::Draft]);

    $this->getJson('/api/v1/buyer/products')
        ->assertOk()
        ->assertJsonPath('data.0.id', $published->id)
        ->assertJsonCount(1, 'data');
});
