<?php

use App\Enums\ProductPublicationState;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// FR-B-001 / SCR-B-001 / AT-B-001: public, bounded, stable catalogue loading.
it('returns four published products with full category metadata for guests and Buyers', function (): void {
    $vegetables = Category::factory()->create(['name' => '野菜']);
    $fruit = Category::factory()->create(['name' => '果物']);
    Product::factory()->count(5)->create([
        'category_id' => $vegetables->id,
        'publication_state' => ProductPublicationState::Published,
        'created_at' => now(),
    ]);
    $older = Product::factory()->create([
        'category_id' => $fruit->id,
        'publication_state' => ProductPublicationState::Published,
        'created_at' => now()->subDay(),
    ]);
    ProductVariant::factory()->create(['product_id' => $older->id, 'stock_quantity' => 0]);
    Product::factory()->create(['publication_state' => ProductPublicationState::Draft]);
    Product::factory()->create(['publication_state' => ProductPublicationState::Unpublished]);
    $deleted = Product::factory()->create(['publication_state' => ProductPublicationState::Published]);
    $deleted->delete();

    $response = $this->getJson('/api/v1/buyer/products/feed')->assertOk()
        ->assertJsonCount(4, 'data.products')->assertJsonPath('data.total', 6);
    expect($response->json('data.categories'))->toContain('野菜', '果物');
    expect($response->json('data.next_cursor'))->toBeString();
    expect($response->json('data.products.0'))->toHaveKeys(['id', 'name', 'variants'])
        ->not->toHaveKeys(['commission', 'settlements', 'payouts']);

    $this->actingAs(User::factory()->buyer()->create())
        ->getJson('/api/v1/buyer/products/feed')->assertOk()->assertJsonCount(4, 'data.products');
});

it('traverses tied timestamps without duplicate or missing products and stops at the final batch', function (): void {
    $products = Product::factory()->count(11)->create([
        'publication_state' => ProductPublicationState::Published,
        'created_at' => now(),
    ]);
    $ids = [];
    $cursor = null;
    $sizes = [];
    do {
        $response = $this->getJson('/api/v1/buyer/products/feed?'.http_build_query(array_filter([
            'cursor' => $cursor,
        ])))->assertOk();
        $batch = $response->json('data.products');
        $sizes[] = count($batch);
        $ids = [...$ids, ...array_column($batch, 'id')];
        $cursor = $response->json('data.next_cursor');
    } while ($cursor !== null && count($sizes) < 5);

    expect($sizes)->toBe([4, 4, 3]);
    expect($ids)->toHaveCount(11);
    expect(array_unique($ids))->toHaveCount(11);
    expect($ids)->toBe($products->sortByDesc('id')->pluck('id')->all());
    expect($cursor)->toBeNull();
});

it('does not shift later batches when a new product is inserted or an earlier product is edited', function (): void {
    $products = Product::factory()->count(9)->create([
        'publication_state' => ProductPublicationState::Published,
        'created_at' => now()->subDay(),
    ]);
    $first = $this->getJson('/api/v1/buyer/products/feed')->assertOk();
    Product::factory()->create(['publication_state' => ProductPublicationState::Published]);
    Product::query()->whereKey($first->json('data.products.0.id'))->update(['updated_at' => now()]);
    $next = $this->getJson('/api/v1/buyer/products/feed?'.http_build_query([
        'cursor' => $first->json('data.next_cursor'),
    ]))->assertOk();
    $expected = $products->sortByDesc('id')->pluck('id')->slice(4, 4)->values()->all();
    expect(array_column($next->json('data.products'), 'id'))->toBe($expected);
});

it('filters every batch by category and safely returns an empty category', function (): void {
    $category = Category::factory()->create(['name' => '野菜']);
    Product::factory()->count(5)->create([
        'category_id' => $category->id,
        'publication_state' => ProductPublicationState::Published,
    ]);
    Product::factory()->count(3)->create(['publication_state' => ProductPublicationState::Published]);
    $first = $this->getJson('/api/v1/buyer/products/feed?'.http_build_query(['category' => '野菜']))
        ->assertOk()->assertJsonPath('data.total', 5)->assertJsonCount(4, 'data.products');
    $next = $this->getJson('/api/v1/buyer/products/feed?'.http_build_query([
        'category' => '野菜', 'cursor' => $first->json('data.next_cursor'),
    ]))->assertOk()->assertJsonCount(1, 'data.products')->assertJsonPath('data.next_cursor', null);
    expect(array_column($next->json('data.products'), 'category'))->toBe(['野菜']);
    $this->getJson('/api/v1/buyer/products/feed?'.http_build_query(['category' => 'missing']))
        ->assertOk()->assertJsonCount(0, 'data.products')->assertJsonPath('data.total', 0)
        ->assertJsonPath('data.next_cursor', null);
});

it('validates cursor and batch-size input', function (array $params, string $field): void {
    $this->getJson('/api/v1/buyer/products/feed?'.http_build_query($params))
        ->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    [['per_page' => 0], 'per_page'],
    [['per_page' => 41], 'per_page'],
    [['per_page' => 'all'], 'per_page'],
    [['cursor' => 'not-a-cursor'], 'cursor'],
    [['cursor' => (new Cursor(['wrong' => 'value']))->encode()], 'cursor'],
    [['category' => ['野菜']], 'category'],
]);

// BR-004 / BR-021 / DATA-004: a guest cart looks up its own public variants only.
it('looks up only requested published variants without loading the full catalogue', function (): void {
    $published = Product::factory()->create(['publication_state' => ProductPublicationState::Published]);
    $variant = ProductVariant::factory()->create(['product_id' => $published->id]);
    $other = Product::factory()->create(['publication_state' => ProductPublicationState::Published]);
    ProductVariant::factory()->create(['product_id' => $other->id]);
    $draft = Product::factory()->create(['publication_state' => ProductPublicationState::Draft]);
    $hidden = ProductVariant::factory()->create(['product_id' => $draft->id]);

    $this->getJson('/api/v1/buyer/products?'.http_build_query([
        'variant_ids' => [$variant->id, $hidden->id],
    ]))->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $published->id);
    $this->getJson('/api/v1/buyer/products?variant_ids[]=bad')
        ->assertUnprocessable()->assertJsonValidationErrors('variant_ids.0');
});

// AT-B-001: continuation requests must not repeat catalogue-wide metadata queries.
it('calculates total and categories only on the first batch', function (): void {
    $category = Category::factory()->create(['name' => '野菜']);
    Product::factory()->count(6)->create([
        'category_id' => $category->id,
        'publication_state' => ProductPublicationState::Published,
    ]);
    $first = $this->getJson('/api/v1/buyer/products/feed?'.http_build_query(['category' => '野菜']))
        ->assertOk()->assertJsonPath('data.total', 6)->assertJsonPath('data.categories.0', '野菜');

    DB::enableQueryLog();
    DB::flushQueryLog();
    $this->getJson('/api/v1/buyer/products/feed?'.http_build_query([
        'category' => '野菜', 'cursor' => $first->json('data.next_cursor'),
    ]))->assertOk()->assertJsonCount(2, 'data.products')
        ->assertJsonPath('data.total', null)->assertJsonPath('data.categories', null);
    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();

    expect($queries->contains(fn (string $sql) => str_contains(strtolower($sql), 'count(*)')))->toBeFalse();
    expect($queries->contains(fn (string $sql) => str_contains(strtolower(str_replace([chr(34), chr(96)], '', $sql)), 'select name from categories')))->toBeFalse();
});

it('indexes publication and category filters with the complete cursor ordering', function (): void {
    $indexes = collect(Schema::getIndexes('products'))->keyBy('name');
    expect($indexes['products_buyer_feed_index']['columns'])
        ->toBe(['publication_state', 'deleted_at', 'created_at', 'id']);
    expect($indexes['products_buyer_category_feed_index']['columns'])
        ->toBe(['category_id', 'publication_state', 'deleted_at', 'created_at', 'id']);
});
