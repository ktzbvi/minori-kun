<?php

use App\Enums\UserRole;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function cartCountFixture(User $buyer, array $quantities, string $state = 'active'): Cart
{
    $cart = Cart::query()->create(['buyer_id' => $buyer->id, 'state' => $state]);
    foreach ($quantities as $quantity) {
        CartItem::query()->create([
            'cart_id' => $cart->id,
            'variant_id' => ProductVariant::factory()->create()->id,
            'quantity' => $quantity,
        ]);
    }

    return $cart;
}

// FR-B-001 / B01-05 / BR-021 / SEC-003: lightweight, Buyer-scoped badge count.
it('returns zero without creating a cart for an empty Buyer', function (): void {
    $this->actingAs(User::factory()->buyer()->create())->getJson('/api/v1/buyer/cart/count')
        ->assertOk()->assertExactJson(['data' => ['count' => 0]]);
    $this->assertDatabaseCount('carts', 0);
});

it('sums quantities only in the signed-in Buyers active carts without loading product details', function (): void {
    $buyer = User::factory()->buyer()->create();
    cartCountFixture($buyer, [2, 3]);
    cartCountFixture($buyer, [9], 'completed');
    $otherBuyer = User::factory()->buyer()->create();
    cartCountFixture($otherBuyer, [7]);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $this->actingAs($buyer)->getJson('/api/v1/buyer/cart/count?buyer_id='.$otherBuyer->id)
        ->assertOk()->assertExactJson(['data' => ['count' => 5]]);
    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();
    expect($queries->contains(fn (string $sql) => str_contains(strtolower($sql), 'products')))->toBeFalse();
    expect($queries->contains(fn (string $sql) => str_contains(strtolower($sql), 'product_variants')))->toBeFalse();
});

it('requires authentication and the Buyer role', function (): void {
    $this->getJson('/api/v1/buyer/cart/count')->assertUnauthorized();
    foreach ([UserRole::Producer, UserRole::Admin] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->getJson('/api/v1/buyer/cart/count')->assertNotFound();
    }
});

it('counts pending guest quantities without duplicating already merged quantities or changing cart data', function (): void {
    $buyer = User::factory()->buyer()->create();
    $cart = cartCountFixture($buyer, [2]);
    $variantId = $cart->items()->first()->variant_id;
    $unmerged = ProductVariant::factory()->create();
    $params = ['guest_items' => [
        ['variant_id' => $variantId, 'quantity' => 3, 'merge_target' => 3],
        ['variant_id' => $unmerged->id, 'quantity' => 2],
    ]];
    foreach (range(1, 2) as $_) {
        $this->actingAs($buyer)->getJson('/api/v1/buyer/cart/count?'.http_build_query($params))
            ->assertOk()->assertExactJson(['data' => ['count' => 5]]);
    }
    expect($cart->items()->sum('quantity'))->toBe(2);
    $this->assertDatabaseCount('cart_items', 1);

    $cart->items()->update(['quantity' => 3]);
    $this->getJson('/api/v1/buyer/cart/count?'.http_build_query($params))
        ->assertOk()->assertExactJson(['data' => ['count' => 5]]);
});

it('does not inspect another Buyers quantities when reconciling a pending variant', function (): void {
    $buyer = User::factory()->buyer()->create();
    $otherCart = cartCountFixture(User::factory()->buyer()->create(), [5]);
    $params = ['guest_items' => [[
        'variant_id' => $otherCart->items()->first()->variant_id,
        'quantity' => 2,
        'merge_target' => 2,
    ]]];
    $this->actingAs($buyer)->getJson('/api/v1/buyer/cart/count?'.http_build_query($params))
        ->assertOk()->assertExactJson(['data' => ['count' => 2]]);
});

it('rejects invalid pending guest count input', function (): void {
    $buyer = User::factory()->buyer()->create();
    $this->actingAs($buyer)->getJson('/api/v1/buyer/cart/count?'.http_build_query([
        'guest_items' => [['variant_id' => 'invalid', 'quantity' => 0, 'merge_target' => -1]],
    ]))->assertUnprocessable()->assertJsonValidationErrors([
        'guest_items.0.variant_id', 'guest_items.0.quantity', 'guest_items.0.merge_target',
    ]);
});
