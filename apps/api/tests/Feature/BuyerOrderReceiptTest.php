<?php

use App\Models\Order;
use App\Models\OrderDeliveryAddress;
use App\Models\OrderItem;
use App\Models\ProducerOrder;
use App\Models\ProducerProfile;
use App\Models\User;

function receiptOrder(User $buyer, User $producer, string $deadline): Order
{
    ProducerProfile::query()->create(['user_id' => $producer->id, 'farm_name' => 'みのり農園']);
    $order = Order::query()->create([
        'buyer_id' => $buyer->id,
        'order_number' => 'EC-20261002-ABC123',
        'order_state' => '注文確定',
        'payment_state' => 'succeeded',
        'refund_state' => 'none',
        'subtotal_yen' => 4260,
        'discount_yen' => 298,
        'shipping_yen' => 0,
        'total_yen' => 3962,
        'cancellation_deadline_at' => $deadline,
        'placed_at' => now()->subMinutes(30),
    ]);
    OrderDeliveryAddress::query()->create([
        'order_id' => $order->id,
        'recipient_name' => '田中 太郎',
        'phone' => '090-1234-5678',
        'postal_code' => '150-0002',
        'prefecture' => '東京都',
        'city' => '渋谷区',
        'address_line1' => '渋谷1-2-3',
    ]);
    $producerOrder = ProducerOrder::query()->create([
        'order_id' => $order->id,
        'producer_id' => $producer->id,
        'shop_name_snapshot' => 'みのり農園',
        'sub_order_number' => $order->order_number.'-01',
        'fulfillment_state' => 'received',
        'subtotal_yen' => 3962,
        'producer_discount_yen' => 298,
        'company_commission_yen' => 396,
        'total_yen' => 3962,
    ]);
    OrderItem::query()->create([
        'producer_order_id' => $producerOrder->id,
        'product_id' => null,
        'variant_id' => null,
        'producer_id' => $producer->id,
        'product_name_snapshot' => '季節の野菜セット',
        'variant_label_snapshot' => '通常',
        'unit_price_yen' => 2980,
        'discount_bps' => 1000,
        'discount_yen' => 298,
        'quantity' => 1,
        'line_total_yen' => 2682,
    ]);

    return $order;
}

it('makes an order complete at its cancellation deadline and serves its receipt', function (): void {
    $this->travelTo(now()->startOfSecond());
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = receiptOrder($buyer, $producer, now()->addMinute()->toDateTimeString());
    ProducerProfile::query()->where('user_id', $producer->id)->update(['farm_name' => '変更後の農園名']);

    $this->actingAs($buyer)->getJson("/api/v1/buyer/orders/{$order->id}/receipt")
        ->assertForbidden();
    expect($order->fresh()->order_state)->toBe('注文確定');

    $this->travel(1)->minutes();
    $response = $this->actingAs($buyer)->get("/api/v1/buyer/orders/{$order->id}/receipt");
    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'attachment; filename="receipt-'.$order->order_number.'.pdf"')
        ->assertHeader('Cache-Control', 'no-store, private');
    expect(substr($response->getContent(), 0, 5))->toBe('%PDF-')
        ->and($response->getContent())->toContain('NotoSansJP')
        ->and(strlen($response->getContent()))->toBeGreaterThan(1000);

    $this->actingAs($buyer)->getJson("/api/v1/buyer/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order_state', '完了')
        ->assertJsonPath('data.can_cancel', false);

    expect($order->fresh()->order_state)->toBe('完了');
});

it('does not disclose a receipt to another Buyer', function (): void {
    $buyer = User::factory()->buyer()->create();
    $otherBuyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $order = receiptOrder($buyer, $producer, now()->subMinute()->toDateTimeString());

    $this->actingAs($otherBuyer)->getJson("/api/v1/buyer/orders/{$order->id}/receipt")
        ->assertNotFound();
});
