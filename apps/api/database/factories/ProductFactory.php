<?php

namespace Database\Factories;

use App\Enums\ProductPublicationState;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producer_id' => User::factory()->producer(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'type_name' => null,
            'publication_state' => ProductPublicationState::Draft,
            'moderation_state' => 'clear',
        ];
    }
}
