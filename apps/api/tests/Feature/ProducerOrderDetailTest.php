<?php

use App\Enums\ProducerOperationalState;
use App\Enums\ScreeningState;
use App\Models\AuditEvent;
use App\Models\Order;
use App\Models\OrderDeliveryAddress;
use App\Models\OrderItem;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerOrder;
use App\Models\ProducerProfile;
use App\Models\User;

function detailProducer(): User
{
    $user = User::factory()->producer()->create();
    ProducerProfile::query()->create(['user_id' => $user->id, 'farm_name' => 'Test farm', 'operational_state' => ProducerOperationalState::Active, 'selling_eligible_at' => now()]);
    $tenant = PayjpTenant::query()->create(['producer_id' => $user->id, 'provider_tenant_reference' => 'ten_'.$user->id, 'application_state' => 'approved', 'bank_state' => 'registered']);
    foreach (['visa', 'mastercard'] as $brand) {
        PayjpScreening::query()->create(['tenant_id' => $tenant->id, 'card_brand' => $brand, 'state' => ScreeningState::Passed]);
    }

    return $user;
}

function detailOrder(User $producer): ProducerOrder
{
    $order = Order::query()->create(['buyer_id' => User::factory()->buyer()->create()->id, 'order_number' => 'O-'.fake()->unique()->numerify('########'), 'order_state' => '注文確定', 'payment_state' => 'succeeded', 'refund_state' => 'none', 'subtotal_yen' => 1000, 'discount_yen' => 100, 'shipping_yen' => 0, 'total_yen' => 900, 'cancellation_deadline_at' => now()->addMinutes(30), 'placed_at' => now()]);
    $record = ProducerOrder::query()->create(['order_id' => $order->id, 'producer_id' => $producer->id, 'sub_order_number' => $order->order_number.'-01', 'fulfillment_state' => 'received', 'subtotal_yen' => 900, 'producer_discount_yen' => 100, 'company_commission_bps' => 1000, 'company_commission_yen' => 90, 'total_yen' => 900]);
    OrderItem::query()->create(['producer_order_id' => $record->id, 'producer_id' => $producer->id, 'product_name_snapshot' => 'Snapshot vegetables', 'variant_label_snapshot' => 'default', 'unit_price_yen' => 1000, 'discount_bps' => 1000, 'discount_yen' => 100, 'quantity' => 1, 'line_total_yen' => 900]);
    OrderDeliveryAddress::query()->create(['order_id' => $order->id, 'recipient_name' => 'Test recipient', 'phone' => '000-0000-0000', 'postal_code' => '000-0000', 'prefecture' => 'Test', 'city' => 'City', 'address_line1' => 'Test address']);

    return $record;
}

// FR-P-009 / SCR-P-009 / P09-01..04 / AT-P-009 / DATA-006 / SEC-001,002,011.
it('reflects fulfillment updates in the existing Buyer order view', function () {
    $producer = detailProducer();
    $record = detailOrder($producer);
    $this->actingAs($producer)->patchJson('/api/v1/producer/orders/'.$record->id.'/fulfillment', [
        'fulfillment_state' => 'shipped', 'expected_state' => 'received',
    ])->assertOk();
    $this->actingAs($record->order->buyer)->getJson('/api/v1/buyer/orders/'.$record->order_id)
        ->assertOk()->assertJsonPath('data.producer_orders.0.fulfillment_state', 'shipped')
        ->assertJsonPath('data.producer_orders.0.items.0.line_total_yen', 900);
});

it('exposes only owned purchase snapshots and minimum delivery data', function () {
    $producer = detailProducer();
    $record = detailOrder($producer);
    $foreign = detailOrder(detailProducer());
    OrderItem::query()->create(['producer_order_id' => $record->id, 'producer_id' => $foreign->producer_id, 'product_name_snapshot' => 'Foreign', 'variant_label_snapshot' => 'default', 'unit_price_yen' => 1, 'discount_bps' => 0, 'discount_yen' => 0, 'quantity' => 1, 'line_total_yen' => 1]);
    $this->actingAs($producer)->getJson('/api/v1/producer/orders/'.$record->id)->assertOk()->assertJsonCount(1, 'data.items')->assertJsonPath('data.items.0.unit_price_yen', 1000)->assertJsonPath('data.items.0.discount_bps', 1000)->assertJsonPath('data.items.0.line_total_yen', 900)->assertJsonPath('data.delivery_address.recipient_name', 'Test recipient')->assertJsonMissingPath('data.buyer_id')->assertJsonMissingPath('data.company_commission_yen');
    $this->getJson('/api/v1/producer/orders/'.$foreign->id)->assertNotFound();
    $this->patchJson('/api/v1/producer/orders/'.$foreign->id.'/fulfillment', ['fulfillment_state' => 'shipped', 'expected_state' => 'received'])->assertNotFound();
    expect($foreign->fresh()->fulfillment_state->value)->toBe('received');
});

it('allows all forward and backward updates and audits each actual change once', function () {
    $producer = detailProducer();
    $record = detailOrder($producer);
    $this->actingAs($producer);
    $previous = 'received';
    foreach (['processing', 'shipped', 'received', 'shipped', 'processing', 'received'] as $state) {
        $input = ['fulfillment_state' => $state, 'expected_state' => $previous];
        foreach ([1, 2] as $attempt) {
            $this->patchJson('/api/v1/producer/orders/'.$record->id.'/fulfillment', $input)->assertOk()->assertJsonPath('data.fulfillment_state', $state);
        }
        $previous = $state;
    }
    expect(AuditEvent::query()->where('target_id', $record->id)->count())->toBe(6);
    $event = AuditEvent::query()->where('target_id', $record->id)->first();
    expect(json_decode($event->safe_before, true))->toBe(['fulfillment_state' => 'received']);
    expect($record->order->fresh()->order_state)->toBe('注文確定');
    $this->patchJson('/api/v1/producer/orders/'.$record->id.'/fulfillment', ['fulfillment_state' => 'processing', 'expected_state' => 'shipped'])->assertConflict();
    expect($record->fresh()->fulfillment_state->value)->toBe('received');
});

it('blocks cancelled refunding and unpaid orders without state or audit changes', function (array $attributes) {
    $producer = detailProducer();
    $record = detailOrder($producer);
    $record->order->update($attributes);
    $this->actingAs($producer)->patchJson('/api/v1/producer/orders/'.$record->id.'/fulfillment', ['fulfillment_state' => 'shipped', 'expected_state' => 'received'])->assertConflict();
    expect($record->fresh()->fulfillment_state->value)->toBe('received');
    expect(AuditEvent::query()->count())->toBe(0);
})->with([[['order_state' => 'cancelled']], [['refund_state' => 'pending']], [['refund_state' => 'refunded']], [['refund_state' => 'partial']], [['refund_state' => 'failed']], [['payment_state' => 'pending']]]);

it('rejects system states and requires producer role and eligibility', function () {
    $producer = detailProducer();
    $record = detailOrder($producer);
    $url = '/api/v1/producer/orders/'.$record->id.'/fulfillment';
    $input = ['fulfillment_state' => 'shipped', 'expected_state' => 'received'];
    $this->patchJson($url, $input)->assertUnauthorized();
    foreach ([User::factory()->buyer()->create(), User::factory()->admin()->create()] as $actor) {
        $this->actingAs($actor)->patchJson($url, $input)->assertNotFound();
    }
    $this->actingAs(User::factory()->producer()->create())->patchJson($url, $input)->assertForbidden();
    $this->actingAs($producer);
    foreach (['confirmed', 'cancelled', 'refunded', 'invalid'] as $state) {
        $this->patchJson($url, ['fulfillment_state' => $state, 'expected_state' => 'received'])->assertUnprocessable();
    }
    $this->patchJson($url, ['fulfillment_state' => 'shipped'])->assertUnprocessable();
    expect($record->fresh()->fulfillment_state->value)->toBe('received');
});
