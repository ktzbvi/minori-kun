<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

it('enforces valid variant price stock and per-option discount values', function (): void {
    $producer = User::factory()->producer()->create();
    $product = Product::factory()->create(['producer_id' => $producer->id]);

    expect(fn () => ProductVariant::query()->create([
        'product_id' => $product->id,
        'option_label' => 'Invalid',
        'price_yen' => 1000,
        'stock_quantity' => 1,
        'discount_bps' => 10001,
        'display_order' => 0,
    ]))->toThrow(ValidationException::class);
});

it('does not persist raw card bank or provider secret fields', function (): void {
    $forbidden = ['card_number', 'security_code', 'cvv', 'bank_account_number', 'payjp_secret_key'];
    $tables = ['users', 'payment_attempts', 'payjp_tenants', 'producer_bank_snapshots'];

    foreach ($tables as $table) {
        expect(array_intersect($forbidden, Schema::getColumnListing($table)))->toBeEmpty();
    }
});

it('keeps company commission and producer discount in separate columns', function (): void {
    expect(Schema::hasColumns('product_variants', ['discount_bps']))->toBeTrue()
        ->and(Schema::hasColumns('producer_orders', ['company_commission_bps', 'company_commission_yen']))->toBeTrue();
});
