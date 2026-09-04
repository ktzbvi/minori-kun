<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductVariant> */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'option_label' => '通常商品',
            'is_default' => true,
            'price_yen' => fake()->numberBetween(500, 10000),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'discount_bps' => 0,
            'display_order' => 0,
        ];
    }
}
