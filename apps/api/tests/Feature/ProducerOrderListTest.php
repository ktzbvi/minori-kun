<?php

use App\Enums\ProducerOperationalState;
use App\Enums\ScreeningState;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerOrder;
use App\Models\ProducerProfile;
use App\Models\User;
use Carbon\CarbonImmutable;

function orderListProducer(): User
{
    $producer = User::factory()->producer()->create();
    ProducerProfile::query()->create([
        'user_id' => $producer->id, 'farm_name' => 'テスト農園',
        'operational_state' => ProducerOperationalState::Active, 'selling_eligible_at' => now(),
    ]);
    $tenant = PayjpTenant::query()->create([
        'producer_id' => $producer->id, 'provider_tenant_reference' => 'ten_'.$producer->id,
        'application_state' => 'approved', 'bank_state' => 'registered',
    ]);
    foreach (['visa', 'mastercard'] as $brand) {
        PayjpScreening::query()->create(['tenant_id' => $tenant->id, 'card_brand' => $brand, 'state' => ScreeningState::Passed]);
    }

    return $producer;
}

function orderListRecord(User $producer, array $orderAttributes = [], string $fulfillment = 'received', string $name = '購入時のトマト'): ProducerOrder
{
    $order = Order::query()->create(array_merge([
        'buyer_id' => User::factory()->buyer()->create()->id,
        'order_number' => 'O-'.fake()->unique()->numerify('########'),
        'order_state' => '注文確定', 'payment_state' => 'succeeded', 'refund_state' => 'none',
        'subtotal_yen' => 1000, 'discount_yen' => 0, 'shipping_yen' => 0, 'total_yen' => 1000,
        'cancellation_deadline_at' => now()->addMinutes(30), 'placed_at' => now(),
    ], $orderAttributes));
    $record = ProducerOrder::query()->create([
        'order_id' => $order->id, 'producer_id' => $producer->id,
        'sub_order_number' => $order->order_number.'-01', 'fulfillment_state' => $fulfillment,
        'subtotal_yen' => 1000, 'producer_discount_yen' => 0,
        'company_commission_bps' => 1000, 'company_commission_yen' => 100, 'total_yen' => 1000,
    ]);
    OrderItem::query()->create([
        'producer_order_id' => $record->id, 'producer_id' => $producer->id,
        'product_id' => null, 'variant_id' => null, 'product_name_snapshot' => $name,
        'variant_label_snapshot' => '通常', 'unit_price_yen' => 1000, 'discount_bps' => 0,
        'discount_yen' => 0, 'quantity' => 2, 'line_total_yen' => 1000,
    ]);

    return $record;
}

// FR-P-008 / SCR-P-008 / AT-P-008 / DATA-006 / SEC-001, SEC-002.
it('lists only owned paid orders and snapshots without financial or Buyer information', function (): void {
    $producer = orderListProducer();
    $other = orderListProducer();
    $own = orderListRecord($producer);
    $foreign = orderListRecord($other, [], 'received', '他生産者の商品');
    orderListRecord($producer, ['payment_state' => 'pending']);
    orderListRecord($producer, ['payment_state' => 'failed']);
    OrderItem::query()->create([
        'producer_order_id' => $own->id, 'producer_id' => $other->id,
        'product_name_snapshot' => '不一致の商品', 'variant_label_snapshot' => '通常',
        'unit_price_yen' => 1000, 'discount_bps' => 0, 'discount_yen' => 0, 'quantity' => 1, 'line_total_yen' => 1000,
    ]);

    $this->actingAs($producer)->getJson('/api/v1/producer/orders')
        ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $own->id)
        ->assertJsonPath('data.0.items.0.product_name', '購入時のトマト')
        ->assertJsonPath('data.0.items.0.quantity', 2)->assertJsonCount(1, 'data.0.items')
        ->assertJsonMissingPath('data.0.company_commission_yen')->assertJsonMissingPath('data.0.buyer_id')
        ->assertJsonMissing(['id' => $foreign->id]);
    $this->getJson('/api/v1/producer/orders?keyword=不一致の商品')->assertJsonPath('meta.total', 0);
    $this->getJson('/api/v1/producer/orders/'.$foreign->id)->assertNotFound();
    $this->getJson('/api/v1/producer/orders/'.$own->id)->assertOk()->assertJsonPath('data.id', $own->id);
});

it('requires Producer authentication, role and eligibility for list and selected order', function (): void {
    $record = orderListRecord(orderListProducer());
    foreach (['/api/v1/producer/orders', '/api/v1/producer/orders/'.$record->id] as $url) {
        $this->getJson($url)->assertUnauthorized();
        foreach ([User::factory()->buyer()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->getJson($url)->assertNotFound();
        }
        $this->actingAs(User::factory()->producer()->create())->getJson($url)->assertForbidden();
        auth()->forgetGuards();
    }
});

it('searches owned snapshots and references and applies fulfillment filters', function (): void {
    $producer = orderListProducer();
    $record = orderListRecord($producer, ['order_number' => 'O-SEARCH'], 'processing', '季節の野菜セット');
    orderListRecord($producer, [], 'shipped');
    orderListRecord(orderListProducer(), [], 'processing', '季節の野菜セット');
    $this->actingAs($producer);
    foreach (['季節', '#O-SEARCH', $record->sub_order_number] as $keyword) {
        $this->getJson('/api/v1/producer/orders?'.http_build_query(['keyword' => $keyword]))
            ->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $record->id);
    }
    $this->getJson('/api/v1/producer/orders?fulfillment_state=shipped')->assertJsonPath('meta.total', 1);
    $this->getJson('/api/v1/producer/orders?keyword=missing')->assertJsonPath('meta.total', 0);
    orderListRecord($producer, [], 'received', '100%_野菜!');
    foreach (['%', '_', '!'] as $keyword) {
        $this->getJson('/api/v1/producer/orders?'.http_build_query(['keyword' => $keyword]))
            ->assertJsonPath('meta.total', 1);
    }
});

it('keeps received during cancellation and presents confirmed at the exact deadline without writes', function (): void {
    $producer = orderListProducer();
    $this->travelTo(CarbonImmutable::parse('2026-10-05 03:00:00', 'UTC'));
    $record = orderListRecord($producer);
    $this->actingAs($producer);
    $this->getJson('/api/v1/producer/orders/'.$record->id)->assertJsonPath('data.operational_state', 'received');
    $this->travel(30)->minutes();
    foreach ([1, 2] as $attempt) {
        $this->getJson('/api/v1/producer/orders/'.$record->id)
            ->assertJsonPath('data.operational_state', 'confirmed')->assertJsonPath('data.status_owner', 'system')
            ->assertJsonPath('data.fulfillment_state', 'received');
    }
    expect($record->fresh()->fulfillment_state->value)->toBe('received');
    expect($record->order->fresh()->order_state)->toBe('注文確定');
    $record->update(['fulfillment_state' => 'processing']);
    $this->getJson('/api/v1/producer/orders/'.$record->id)
        ->assertJsonPath('data.operational_state', 'processing')->assertJsonPath('data.status_owner', 'producer');
    $record->order->update(['order_state' => 'cancelled', 'payment_state' => 'refunded', 'refund_state' => 'refunded']);
    $this->getJson('/api/v1/producer/orders/'.$record->id)
        ->assertJsonPath('data.operational_state', 'cancelled')->assertJsonPath('data.status_owner', 'system');
});

it('does not present refunding orders as confirmed or Producer editable', function (): void {
    $producer = orderListProducer();
    $record = orderListRecord($producer, ['refund_state' => 'pending', 'cancellation_deadline_at' => now()->subMinute()]);
    $this->actingAs($producer)->getJson('/api/v1/producer/orders/'.$record->id)
        ->assertOk()->assertJsonPath('data.operational_state', 'received')
        ->assertJsonPath('data.refund_state', 'pending')->assertJsonPath('data.status_owner', 'system');
});

it('keeps refunds separate from cancellation and fulfillment', function (): void {
    $producer = orderListProducer();
    $record = orderListRecord($producer, [
        'order_state' => '完了', 'payment_state' => 'refunded', 'refund_state' => 'refunded',
    ], 'shipped');
    $this->actingAs($producer)->getJson('/api/v1/producer/orders/'.$record->id)
        ->assertOk()->assertJsonPath('data.operational_state', 'shipped')
        ->assertJsonPath('data.refund_state', 'refunded')->assertJsonPath('data.status_owner', 'system');
});

it('filters mobile status chips using independent order refund and fulfillment states', function (): void {
    $producer = orderListProducer();
    orderListRecord($producer, [], 'received');
    orderListRecord($producer, [], 'processing');
    orderListRecord($producer, [], 'shipped');
    orderListRecord($producer, ['order_state' => 'cancelled', 'refund_state' => 'pending']);
    orderListRecord($producer, ['order_state' => 'cancelled', 'payment_state' => 'refunded', 'refund_state' => 'refunded']);
    orderListRecord($producer, ['order_state' => '完了', 'payment_state' => 'refunded', 'refund_state' => 'refunded'], 'shipped');
    orderListRecord(orderListProducer(), ['order_state' => 'cancelled', 'refund_state' => 'refunded']);
    $this->actingAs($producer);
    foreach (['all' => 6, 'received' => 1, 'processing' => 1, 'shipped' => 1, 'cancelled' => 2, 'refunded' => 2] as $status => $count) {
        $this->getJson('/api/v1/producer/orders?status='.$status)->assertOk()->assertJsonPath('meta.total', $count);
    }
    $this->getJson('/api/v1/producer/orders?status=confirmed')->assertUnprocessable();
});

it('applies all required periods and inclusive Tokyo calendar boundaries', function (): void {
    $producer = orderListProducer();
    $this->travelTo(CarbonImmutable::parse('2026-10-05 12:00:00', 'Asia/Tokyo'));
    foreach (['2026-10-01', '2026-08-01', '2026-01-01', '2025-11-01', '2024-12-31'] as $date) {
        orderListRecord($producer, ['placed_at' => CarbonImmutable::parse($date, 'Asia/Tokyo')->utc()]);
    }
    $this->actingAs($producer);
    foreach (['all' => 5, '30d' => 1, '90d' => 2, '12m' => 4, 'year' => 3, 'custom' => 1] as $period => $total) {
        $this->getJson('/api/v1/producer/orders?'.http_build_query([
            'period' => $period, 'year' => 2026, 'from' => '2026-10-01', 'to' => '2026-10-01',
        ]))->assertOk()->assertJsonPath('meta.total', $total);
    }
    orderListRecord($producer, ['placed_at' => CarbonImmutable::parse('2026-10-01 23:59:59', 'Asia/Tokyo')->utc()]);
    orderListRecord($producer, ['placed_at' => CarbonImmutable::parse('2026-10-02 00:00:00', 'Asia/Tokyo')->utc()]);
    $this->getJson('/api/v1/producer/orders?period=custom&from=2026-10-01&to=2026-10-01')->assertJsonPath('meta.total', 2);
});

it('paginates newest first with stable totals', function (): void {
    $producer = orderListProducer();
    foreach (range(1, 21) as $index) {
        $latest = orderListRecord($producer, ['placed_at' => now()->subMinutes(22 - $index)]);
    }
    $this->actingAs($producer)->getJson('/api/v1/producer/orders')
        ->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 21)->assertJsonPath('data.0.id', $latest->id);
    $this->getJson('/api/v1/producer/orders?page=2')->assertJsonCount(1, 'data')->assertJsonPath('meta.current_page', 2);
});

it('rejects invalid filters and unavailable manual states', function (array $filters): void {
    $this->actingAs(orderListProducer())->getJson('/api/v1/producer/orders?'.http_build_query($filters))
        ->assertUnprocessable();
})->with([
    [['fulfillment_state' => 'confirmed']], [['fulfillment_state' => 'cancelled']], [['period' => 'invalid']],
    [['period' => 'custom']], [['period' => 'custom', 'from' => '2026-10-02', 'to' => '2026-10-01']],
    [['period' => 'custom', 'from' => '2026-02-30', 'to' => '2026-03-01']],
    [['period' => 'year']], [['period' => 'year', 'year' => 1999]], [['page' => 0]], [['keyword' => str_repeat('a', 256)]],
]);
