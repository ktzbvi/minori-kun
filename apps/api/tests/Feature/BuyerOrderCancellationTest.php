<?php

use App\Jobs\SendBuyerOrderRefundEmail;
use App\Mail\BuyerOrderCancelledMail;
use App\Mail\BuyerOrderRefundCompletedMail;
use App\Models\BuyerOrderEmailOutbox;
use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\PayjpTenant;
use App\Models\PaymentAttempt;
use App\Models\ProducerOrder;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function cancellableBuyerOrder(User $buyer, User $producer): Order
{
    $tenant = PayjpTenant::query()->create([
        'producer_id' => $producer->id,
        'provider_tenant_reference' => 'ten_'.$producer->id,
        'application_state' => 'approved',
        'bank_state' => 'registered',
    ]);
    $order = Order::query()->create([
        'buyer_id' => $buyer->id,
        'order_number' => 'EC-20261005-CANCEL1',
        'order_state' => '注文確定',
        'payment_state' => 'succeeded',
        'refund_state' => 'none',
        'subtotal_yen' => 1200,
        'discount_yen' => 0,
        'shipping_yen' => 0,
        'total_yen' => 1200,
        'cancellation_deadline_at' => now()->addMinutes(30),
        'placed_at' => now(),
    ]);
    $producerOrder = ProducerOrder::query()->create([
        'order_id' => $order->id,
        'producer_id' => $producer->id,
        'shop_name_snapshot' => 'みのり農園',
        'sub_order_number' => 'EC-20261005-CANCEL1-01',
        'fulfillment_state' => 'received',
        'subtotal_yen' => 1200,
        'producer_discount_yen' => 0,
        'company_commission_yen' => 120,
        'total_yen' => 1200,
    ]);
    PaymentAttempt::query()->create([
        'producer_order_id' => $producerOrder->id,
        'payjp_tenant_id' => $tenant->id,
        'provider_payment_reference' => 'pay_'.$order->id,
        'idempotency_reference' => 'charge_'.$order->id,
        'amount_yen' => 1200,
        'payment_state' => 'succeeded',
    ]);

    return $order;
}

it('cancels an order while keeping its pending refund state and does not repeat the refund', function (): void {
    Mail::fake();
    config()->set('buyer_payment.driver', 'fake');
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = cancellableBuyerOrder($buyer, $producer);
    $payload = ['idempotency_key' => 'cancel-test-'.$order->id];

    $this->actingAs($buyer)
        ->postJson("/api/v1/buyer/orders/{$order->id}/cancel", $payload)
        ->assertOk()
        ->assertJsonPath('data.order_state', 'cancelled')
        ->assertJsonPath('data.payment_state', 'succeeded')
        ->assertJsonPath('data.refund_state', 'pending')
        ->assertJsonPath('data.can_cancel', false);

    $this->actingAs($buyer)
        ->postJson("/api/v1/buyer/orders/{$order->id}/cancel", $payload)
        ->assertOk()
        ->assertJsonPath('data.refund_state', 'pending');

    expect(Refund::query()->count())->toBe(1)
        ->and(BuyerOrderEmailOutbox::query()->where('event_type', 'order_cancelled')->count())->toBe(1)
        ->and(OrderCancellation::query()->count())->toBe(1)
        ->and($order->fresh()->refund_state->value)->toBe('pending');

    Mail::assertSent(BuyerOrderCancelledMail::class, 1);
    Mail::assertNotSent(BuyerOrderRefundCompletedMail::class);
});

it('sends the completion email once after an authoritative refund success', function (): void {
    Mail::fake();
    config()->set('buyer_payment.driver', 'fake');
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = cancellableBuyerOrder($buyer, $producer);

    $this->actingAs($buyer)->postJson("/api/v1/buyer/orders/{$order->id}/cancel", [
        'idempotency_key' => 'cancel-test-'.$order->id,
    ])->assertOk()->assertJsonPath('data.refund_state', 'pending');

    $this->artisan('buyer:fake-refund-status', ['order' => $order->order_number, 'status' => 'succeeded'])
        ->assertSuccessful();
    $this->artisan('buyer:fake-refund-status', ['order' => $order->order_number, 'status' => 'pending'])
        ->assertSuccessful();
    $this->artisan('buyer:fake-refund-status', ['order' => $order->order_number, 'status' => 'succeeded'])
        ->assertSuccessful();

    $completionEmail = BuyerOrderEmailOutbox::query()->where('event_type', 'refund_completed')->firstOrFail();
    (new SendBuyerOrderRefundEmail($completionEmail->id))->handle();

    expect($order->fresh()->refund_state->value)->toBe('refunded')
        ->and($order->fresh()->payment_state->value)->toBe('refunded')
        ->and(BuyerOrderEmailOutbox::query()->where('event_type', 'refund_completed')->count())->toBe(1);

    Mail::assertSent(BuyerOrderCancelledMail::class, 1);
    Mail::assertSent(BuyerOrderRefundCompletedMail::class, 1);
});

it('marks a failed refund for follow-up without sending a completion email', function (): void {
    Mail::fake();
    config()->set('buyer_payment.driver', 'fake');
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = cancellableBuyerOrder($buyer, $producer);

    $this->actingAs($buyer)->postJson("/api/v1/buyer/orders/{$order->id}/cancel", [
        'idempotency_key' => 'cancel-test-'.$order->id,
    ])->assertOk();

    $this->artisan('buyer:fake-refund-status', ['order' => $order->order_number, 'status' => 'failed'])
        ->assertSuccessful();

    expect($order->fresh()->order_state)->toBe('cancelled')
        ->and($order->fresh()->refund_state->value)->toBe('failed')
        ->and($order->fresh()->payment_state->value)->toBe('succeeded');

    Mail::assertSent(BuyerOrderCancelledMail::class, 1);
    Mail::assertNotSent(BuyerOrderRefundCompletedMail::class);
});
