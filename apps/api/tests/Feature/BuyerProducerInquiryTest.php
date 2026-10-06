<?php

use App\Mail\BuyerProducerInquiryMail;
use App\Models\BuyerInquiry;
use App\Models\Order;
use App\Models\ProducerOrder;
use App\Models\ProducerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function inquiryOrder(User $buyer, User $producer): Order
{
    ProducerProfile::query()->create(['user_id' => $producer->id, 'farm_name' => 'みのり農園']);
    $order = Order::query()->create([
        'buyer_id' => $buyer->id,
        'order_number' => 'EC-20261005-ABC123',
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
    ProducerOrder::query()->create([
        'order_id' => $order->id,
        'producer_id' => $producer->id,
        'shop_name_snapshot' => 'みのり農園',
        'sub_order_number' => 'EC-20261005-ABC123-01',
        'fulfillment_state' => 'received',
        'subtotal_yen' => 1200,
        'producer_discount_yen' => 0,
        'company_commission_yen' => 120,
        'total_yen' => 1200,
    ]);

    return $order;
}

beforeEach(function (): void {
    Mail::fake();
});

it('sends an owned order inquiry to its Producer without exposing Buyer contact details', function (): void {
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = inquiryOrder($buyer, $producer);
    $payload = [
        'topic' => '配送・到着予定について',
        'message' => '到着予定日を教えてください。',
        'idempotency_key' => '04d92a3d-e0d2-4a2b-a990-255a015adc4e',
    ];

    $this->actingAs($buyer)
        ->postJson("/api/v1/buyer/orders/{$order->id}/producer-inquiries", $payload)
        ->assertCreated()
        ->assertJsonPath('data.reference_number', fn (string $reference): bool => str_starts_with($reference, 'INQ-'));

    expect(BuyerInquiry::query()->where('buyer_id', $buyer->id)->where('producer_order_id', $order->producerOrders()->value('id'))->count())->toBe(1);
    Mail::assertSent(BuyerProducerInquiryMail::class, function (BuyerProducerInquiryMail $mail) use ($producer, $buyer): bool {
        $rendered = $mail->render();

        return $mail->hasTo($producer->email)
            && str_contains($rendered, 'EC-20261005-ABC123')
            && str_contains($rendered, '到着予定日を教えてください。')
            && ! str_contains($rendered, $buyer->email);
    });
});

it('does not duplicate a Producer inquiry or email when retried with the same key', function (): void {
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = inquiryOrder($buyer, $producer);
    $payload = [
        'topic' => '商品について',
        'message' => '商品の内容を確認したいです。',
        'idempotency_key' => '69b29f81-c0c8-41bf-a38e-75f01aa97141',
    ];

    $this->actingAs($buyer)->postJson("/api/v1/buyer/orders/{$order->id}/producer-inquiries", $payload)->assertCreated();
    $this->actingAs($buyer)->postJson("/api/v1/buyer/orders/{$order->id}/producer-inquiries", $payload)->assertOk();

    expect(BuyerInquiry::query()->where('buyer_id', $buyer->id)->count())->toBe(1);
    Mail::assertSent(BuyerProducerInquiryMail::class, 1);
});

it('does not allow another Buyer to submit an order-linked Producer inquiry', function (): void {
    $owner = User::factory()->buyer()->create();
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = inquiryOrder($owner, $producer);

    $this->actingAs($buyer)
        ->postJson("/api/v1/buyer/orders/{$order->id}/producer-inquiries", [
            'topic' => 'その他',
            'message' => '問い合わせ',
            'idempotency_key' => 'bb104931-f437-45ec-8434-cc2617949b0b',
        ])
        ->assertNotFound();

    Mail::assertNothingSent();
});
