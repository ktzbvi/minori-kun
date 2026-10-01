<?php

use App\Enums\FulfillmentState;
use App\Enums\PaymentState;
use App\Enums\PayoutState;
use App\Enums\ProductPublicationState;
use App\Enums\ProducerOperationalState;
use App\Enums\RefundState;
use App\Enums\ScreeningState;
use App\Enums\SettlementState;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\Payout;
use App\Models\ProducerOrder;
use App\Models\ProducerProfile;
use App\Models\ProducerSettlement;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

function dashboardEligibleProducer(array $attributes = []): User
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

function dashboardProducerOrder(User $producer, array $attributes = []): ProducerOrder
{
    $buyer = User::factory()->buyer()->create();
    $order = Order::query()->create([
        'buyer_id' => $buyer->id,
        'order_number' => $attributes['order_number'] ?? fake()->unique()->numerify('O-####'),
        'order_state' => 'received',
        'payment_state' => $attributes['payment_state'] ?? PaymentState::Succeeded,
        'refund_state' => RefundState::None,
        'subtotal_yen' => $attributes['subtotal_yen'] ?? 1000,
        'discount_yen' => 0,
        'shipping_yen' => 0,
        'total_yen' => $attributes['total_yen'] ?? 1000,
        'cancellation_deadline_at' => now()->addMinutes(30),
        'placed_at' => $attributes['placed_at'] ?? now(),
    ]);

    return ProducerOrder::query()->create([
        'order_id' => $order->id,
        'producer_id' => $producer->id,
        'sub_order_number' => $attributes['sub_order_number'] ?? fake()->unique()->numerify('P-####'),
        'fulfillment_state' => $attributes['fulfillment_state'] ?? FulfillmentState::Received,
        'subtotal_yen' => $attributes['subtotal_yen'] ?? 1000,
        'producer_discount_yen' => 0,
        'company_commission_bps' => 1000,
        'company_commission_yen' => 100,
        'total_yen' => $attributes['total_yen'] ?? 1000,
    ]);
}

it('returns dashboard summaries scoped to the authenticated eligible Producer', function (): void {
    $producer = dashboardEligibleProducer();
    $otherProducer = dashboardEligibleProducer();

    $publishedProduct = Product::factory()->create([
        'producer_id' => $producer->id,
        'name' => '高原トマト',
        'publication_state' => ProductPublicationState::Published,
    ]);
    ProductVariant::factory()->create(['product_id' => $publishedProduct->id]);
    Product::factory()->create(['producer_id' => $producer->id, 'publication_state' => ProductPublicationState::Draft]);
    Product::factory()->create(['producer_id' => $otherProducer->id, 'publication_state' => ProductPublicationState::Published]);

    $receivedOrder = dashboardProducerOrder($producer, [
        'sub_order_number' => 'P-0828-001',
        'fulfillment_state' => FulfillmentState::Received,
        'total_yen' => 4200,
    ]);
    OrderItem::query()->create([
        'producer_order_id' => $receivedOrder->id,
        'product_id' => $publishedProduct->id,
        'variant_id' => null,
        'producer_id' => $producer->id,
        'product_name_snapshot' => '高原トマト',
        'variant_label_snapshot' => '通常商品',
        'unit_price_yen' => 4200,
        'discount_bps' => 0,
        'discount_yen' => 0,
        'quantity' => 1,
        'line_total_yen' => 4200,
    ]);
    dashboardProducerOrder($producer, [
        'fulfillment_state' => FulfillmentState::Processing,
        'total_yen' => 2100,
    ]);
    dashboardProducerOrder($producer, [
        'fulfillment_state' => FulfillmentState::Shipped,
        'total_yen' => 3300,
    ]);
    dashboardProducerOrder($otherProducer, [
        'sub_order_number' => 'P-OTHER-001',
        'fulfillment_state' => FulfillmentState::Received,
        'total_yen' => 9999,
    ]);

    $settlement = ProducerSettlement::query()->create([
        'producer_id' => $producer->id,
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
        'state' => SettlementState::Finalized,
        'expected_payout_yen' => 5600,
        'finalized_at' => now(),
    ]);
    Payout::query()->create([
        'settlement_id' => $settlement->id,
        'expected_amount_yen' => 5600,
        'due_on' => now()->addMonth()->endOfMonth()->toDateString(),
        'state' => PayoutState::Scheduled,
    ]);

    $this->actingAs($producer);

    $this->getJson('/api/v1/producer/dashboard')
        ->assertOk()
        ->assertJsonPath('data.products.total', 2)
        ->assertJsonPath('data.products.published', 1)
        ->assertJsonPath('data.orders.requiring_action', 2)
        ->assertJsonPath('data.orders.received', 1)
        ->assertJsonPath('data.orders.processing', 1)
        ->assertJsonPath('data.sales.total_yen', 9600)
        ->assertJsonPath('data.payout_alert.expected_payout_yen', 5600)
        ->assertJsonPath('data.payout_alert.state', 'scheduled')
        ->assertJsonMissingPath('data.action_orders.2')
        ->assertJsonFragment([
            'display_id' => 'P-0828-001',
            'product_summary' => '高原トマト',
        ])
        ->assertJsonMissing(['display_id' => 'P-OTHER-001']);
});

it('requires Producer authentication and selling eligibility', function (): void {
    $producer = User::factory()->producer()->create();
    ProducerProfile::query()->create([
        'user_id' => $producer->id,
        'farm_name' => '準備中農園',
        'operational_state' => ProducerOperationalState::Onboarding,
    ]);

    $this->getJson('/api/v1/producer/dashboard')->assertUnauthorized();

    $this->actingAs($producer);
    $this->getJson('/api/v1/producer/dashboard')->assertForbidden();
});
