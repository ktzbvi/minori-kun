<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentState;
use App\Enums\ProductPublicationState;
use App\Enums\RefundState;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderCancellation;
use App\Models\OrderDeliveryAddress;
use App\Models\OrderItem;
use App\Models\PaymentAttempt;
use App\Models\ProducerOrder;
use App\Models\ProductVariant;
use App\Models\Refund;
use App\Models\User;
use App\Services\Buyer\FakePaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerOrderController extends Controller
{
    public function paymentMode(): JsonResponse
    {
        return response()->json(['data' => [
            'fake_enabled' => app()->environment(['local', 'testing']) && config('buyer_payment.driver') === 'fake',
        ]]);
    }

    public function checkout(Request $request, FakePaymentGateway $gateway): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']) && config('buyer_payment.driver') === 'fake', 503, 'Payment gateway is not configured.');

        $data = $request->validate([
            'producer_id' => ['required', 'ulid'],
            'idempotency_key' => ['required', 'string', 'max:100'],
            'delivery_address.name' => ['required', 'string', 'max:255'],
            'delivery_address.phone' => ['required', 'string', 'max:32'],
            'delivery_address.postal_code' => ['required', 'string', 'max:16'],
            'delivery_address.prefecture' => ['required', 'string', 'max:64'],
            'delivery_address.city' => ['required', 'string', 'max:255'],
            'delivery_address.address_line1' => ['required', 'string', 'max:255'],
            'delivery_address.address_line2' => ['nullable', 'string', 'max:255'],
        ]);

        $buyer = $request->user();
        $existing = Order::query()->where('buyer_id', $buyer->id)
            ->where('checkout_key', $data['idempotency_key'])->first();
        if ($existing) {
            return response()->json(['data' => $this->orderPayload($existing->id)], 200);
        }

        $orderId = DB::transaction(function () use ($buyer, $data, $gateway): string {
            $cart = Cart::query()->where('buyer_id', $buyer->id)->where('state', 'active')
                ->with([
                    'items.variant.product.images',
                    'items.variant.product.producer.producerProfile',
                    'items.variant.product.producer.payjpTenant',
                ])
                ->lockForUpdate()->first();
            $items = $cart?->items->filter(fn ($item) => $item->variant?->product?->producer_id === $data['producer_id'])->values();
            if (! $cart || ! $items || $items->isEmpty()) {
                throw ValidationException::withMessages(['producer_id' => ['選択したショップの商品がカートにありません。']]);
            }

            $producer = User::query()->with(['producerProfile', 'payjpTenant'])->findOrFail($data['producer_id']);
            if (! $producer->isSellingEligible() || ! $producer->payjpTenant) {
                throw ValidationException::withMessages(['producer_id' => ['このショップは現在購入できません。']]);
            }

            $subtotal = 0;
            $discountTotal = 0;
            $feeCandidates = [];
            $priced = [];
            foreach ($items as $cartItem) {
                $variant = ProductVariant::query()->with('product.images')->lockForUpdate()->findOrFail($cartItem->variant_id);
                if ($variant->product->producer_id !== $data['producer_id']
                    || $variant->product->publication_state !== ProductPublicationState::Published
                    || $variant->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages(['cart' => ['商品または在庫が変更されました。カートを確認してください。']]);
                }

                $gross = $variant->price_yen * $cartItem->quantity;
                $lineDiscount = (int) round($gross * $variant->discount_bps / 10000);
                $zoneFee = match ($data['delivery_address']['prefecture']) {
                    '北海道' => $variant->product->delivery_fee_hokkaido_yen,
                    '沖縄県' => $variant->product->delivery_fee_okinawa_yen,
                    default => $variant->product->delivery_fee_honshu_yen,
                };
                $feeCandidates[] = (int) round($zoneFee * (10000 - $variant->discount_bps) / 10000);
                $subtotal += $gross;
                $discountTotal += $lineDiscount;
                $priced[] = [$cartItem, $variant, $gross, $lineDiscount];
            }

            $shipping = max($feeCandidates ?: [0]);
            $total = $subtotal - $discountTotal + $shipping;
            $orderNumber = 'EC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            $order = Order::query()->create([
                'buyer_id' => $buyer->id,
                'order_number' => $orderNumber,
                'checkout_key' => $data['idempotency_key'],
                'order_state' => '注文確定',
                'payment_state' => 'pending',
                'refund_state' => 'none',
                'subtotal_yen' => $subtotal,
                'discount_yen' => $discountTotal,
                'shipping_yen' => $shipping,
                'total_yen' => $total,
                'cancellation_deadline_at' => now()->addMinutes(30),
                'placed_at' => now(),
            ]);
            $address = $data['delivery_address'];
            OrderDeliveryAddress::query()->create([
                'order_id' => $order->id,
                'recipient_name' => $address['name'], 'phone' => $address['phone'],
                'postal_code' => $address['postal_code'], 'prefecture' => $address['prefecture'],
                'city' => $address['city'], 'address_line1' => $address['address_line1'],
                'address_line2' => $address['address_line2'] ?? null,
            ]);

            $producerOrder = ProducerOrder::query()->create([
                'order_id' => $order->id,
                'producer_id' => $producer->id,
                'shop_name_snapshot' => $producer->producerProfile?->farm_name,
                'sub_order_number' => $orderNumber.'-01',
                'fulfillment_state' => 'received',
                'subtotal_yen' => $subtotal - $discountTotal,
                'producer_discount_yen' => $discountTotal,
                'company_commission_bps' => 1000,
                'company_commission_yen' => (int) round(($subtotal - $discountTotal) * .1),
                'total_yen' => $total,
            ]);

            foreach ($priced as [$cartItem, $variant, $gross, $lineDiscount]) {
                $image = $variant->product->images->first();
                OrderItem::query()->create([
                    'producer_order_id' => $producerOrder->id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'producer_id' => $producer->id,
                    'product_name_snapshot' => $variant->product->name,
                    'variant_label_snapshot' => $variant->option_label,
                    'unit_price_yen' => $variant->price_yen,
                    'discount_bps' => $variant->discount_bps,
                    'discount_yen' => $lineDiscount,
                    'quantity' => $cartItem->quantity,
                    'line_total_yen' => $gross - $lineDiscount,
                    'image_url_snapshot' => $image ? Storage::disk($image->disk)->url($image->object_path) : null,
                ]);
            }

            $attempt = PaymentAttempt::query()->create([
                'producer_order_id' => $producerOrder->id,
                'payjp_tenant_id' => $producer->payjpTenant->id,
                'idempotency_reference' => 'checkout:'.$data['idempotency_key'],
                'amount_yen' => $total,
                'payment_state' => 'pending',
            ]);
            $result = $gateway->charge($total, $data['idempotency_key']);
            $attempt->update([
                'provider_payment_reference' => $result['reference'],
                'payment_state' => 'succeeded',
                'authoritative_updated_at' => now(),
            ]);
            $order->update([
                'payment_state' => 'succeeded',
                'cancellation_deadline_at' => now()->addMinutes(30),
            ]);

            foreach ($priced as [$cartItem, $variant]) {
                $variant->decrement('stock_quantity', $cartItem->quantity);
                InventoryMovement::query()->create([
                    'variant_id' => $variant->id, 'quantity_delta' => -$cartItem->quantity,
                    'reason' => 'buyer_order_paid', 'source_type' => 'order',
                    'source_id' => $order->id, 'occurred_at' => now(),
                ]);
                $cartItem->delete();
            }

            return $order->id;
        }, 3);

        return response()->json(['data' => $this->orderPayload($orderId)], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['sometimes', 'in:all,30d,90d,12m,year,custom'],
            'year' => ['required_if:period,year', 'integer', 'between:2000,2100'],
            'from' => ['required_if:period,custom', 'date'],
            'to' => ['required_if:period,custom', 'date', 'after_or_equal:from'],
        ]);
        $query = Order::query()->where('buyer_id', $request->user()->id);
        $period = $request->query('period', 'all');
        $now = now();
        match ($period) {
            '30d' => $query->where('placed_at', '>=', $now->copy()->subDays(30)),
            '90d' => $query->where('placed_at', '>=', $now->copy()->subDays(90)),
            '12m' => $query->where('placed_at', '>=', $now->copy()->subMonths(12)),
            'year' => $query->whereYear('placed_at', (int) $request->query('year', $now->year)),
            'custom' => $query->whereBetween('placed_at', [
                Carbon::parse($request->query('from'))->startOfDay(),
                Carbon::parse($request->query('to'))->endOfDay(),
            ]),
            default => null,
        };

        return response()->json(['data' => $query->with('producerOrders.producer.producerProfile', 'producerOrders.items')
            ->latest('placed_at')->paginate(20)->through(fn (Order $order) => $this->summary($order))]);
    }

    public function show(Request $request, string $order): JsonResponse
    {
        $record = Order::query()->where('buyer_id', $request->user()->id)->whereKey($order)->firstOrFail();
        $this->completeExpiredOrder($record);

        return response()->json(['data' => $this->orderPayload($record->id)]);
    }

    public function receipt(Request $request, string $order): Response
    {
        $record = Order::query()->where('buyer_id', $request->user()->id)->whereKey($order)->firstOrFail();
        $this->completeExpiredOrder($record);
        abort_unless($record->order_state === '完了' && $record->payment_state === PaymentState::Succeeded, 403);

        $record->load('deliveryAddress', 'producerOrders.producer.producerProfile', 'producerOrders.items');
        $producerOrder = $record->producerOrders->firstOrFail();

        $paidAt = PaymentAttempt::query()->where('producer_order_id', $producerOrder->id)
            ->value('authoritative_updated_at') ?? $record->placed_at;
        $shopName = $producerOrder->shop_name_snapshot
            ?? $producerOrder->producer->producerProfile?->farm_name;
        $receiptNumber = 'R-'.$record->placed_at->format('Ymd').'-'.substr($record->order_number, -6);
        $html = view('receipts.buyer-order', [
            'order' => $record,
            'producerOrder' => $producerOrder,
            'shopName' => $shopName,
            'receiptNumber' => $receiptNumber,
            'issuedAt' => now(),
            'paidAt' => $paidAt,
            'issuer' => [
                'name' => 'みのり農園',
                'postal_code' => '370-0000',
                'address' => '群馬県高崎市みのり町1-2-3',
            ],
        ])->render();

        if (! defined('K_PATH_FONTS')) {
            define('K_PATH_FONTS', resource_path('fonts'));
        }
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name'));
        $pdf->SetTitle('領収書 '.$record->order_number);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(18, 16, 18);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->SetFont('cid0jp', '', 10);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        return response($pdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-'.$record->order_number.'.pdf"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function cancel(Request $request, string $order, FakePaymentGateway $gateway): JsonResponse
    {
        abort_unless(app()->environment(['local', 'testing']) && config('buyer_payment.driver') === 'fake', 503, 'Payment gateway is not configured.');
        $request->validate(['idempotency_key' => ['required', 'string', 'max:100']]);

        $record = DB::transaction(function () use ($request, $order, $gateway): Order {
            $record = Order::query()->where('buyer_id', $request->user()->id)->whereKey($order)->lockForUpdate()->firstOrFail();
            if ($record->refund_state === RefundState::Refunded) {
                return $record;
            }
            if (now()->greaterThanOrEqualTo($record->cancellation_deadline_at)) {
                throw ValidationException::withMessages(['order' => ['キャンセル期限を過ぎています。']]);
            }
            if ($record->payment_state !== PaymentState::Succeeded) {
                throw ValidationException::withMessages(['order' => ['この注文はキャンセルできません。']]);
            }

            $attempt = PaymentAttempt::query()->whereHas('producerOrder', fn ($q) => $q->where('order_id', $record->id))->lockForUpdate()->firstOrFail();
            $key = 'cancel:'.$record->id;
            $refundResult = $gateway->refund($record->total_yen, $key);
            Refund::query()->firstOrCreate(['idempotency_reference' => $key], [
                'payment_attempt_id' => $attempt->id, 'amount_yen' => $record->total_yen,
                'reason' => 'buyer_cancellation', 'state' => $refundResult['state'],
                'provider_refund_reference' => $refundResult['reference'],
                'authoritative_updated_at' => now(),
            ]);
            $attempt->update(['payment_state' => 'refunded', 'authoritative_updated_at' => now()]);
            $items = OrderItem::query()->whereIn('producer_order_id', ProducerOrder::query()->where('order_id', $record->id)->select('id'))->get();
            foreach ($items as $item) {
                if (! $item->variant_id) {
                    continue;
                }
                $variant = ProductVariant::query()->lockForUpdate()->find($item->variant_id);
                if (! $variant) {
                    continue;
                }
                $variant->increment('stock_quantity', $item->quantity);
                InventoryMovement::query()->create([
                    'variant_id' => $variant->id, 'quantity_delta' => $item->quantity,
                    'reason' => 'buyer_order_cancelled', 'source_type' => 'order',
                    'source_id' => $record->id, 'occurred_at' => now(),
                ]);
            }
            $record->update(['order_state' => 'cancelled', 'payment_state' => 'refunded', 'refund_state' => 'refunded']);
            OrderCancellation::query()->firstOrCreate(['order_id' => $record->id], [
                'requested_by' => $request->user()->id, 'state' => 'completed',
                'idempotency_reference' => $key, 'requested_at' => now(), 'completed_at' => now(),
            ]);

            return $record->fresh();
        }, 3);

        return response()->json(['data' => $this->orderPayload($record->id)]);
    }

    /** @return array<string, mixed> */
    private function orderPayload(string $id): array
    {
        $order = Order::query()->with('deliveryAddress', 'producerOrders.producer.producerProfile', 'producerOrders.items')
            ->findOrFail($id);
        $this->completeExpiredOrder($order);

        return [
            ...$this->summary($order),
            'subtotal_yen' => $order->subtotal_yen,
            'discount_yen' => $order->discount_yen,
            'shipping_yen' => $order->shipping_yen,
            'delivery_address' => $order->deliveryAddress,
            'cancellation_deadline_at' => $order->cancellation_deadline_at,
            'can_cancel' => $order->payment_state === PaymentState::Succeeded && now()->lessThan($order->cancellation_deadline_at),
            'producer_orders' => $order->producerOrders->map(fn (ProducerOrder $producerOrder) => [
                'producer_id' => $producerOrder->producer_id,
                'shop_name' => $producerOrder->producer->producerProfile?->farm_name,
                'fulfillment_state' => $producerOrder->fulfillment_state->value,
                'items' => $producerOrder->items->map(fn (OrderItem $item) => [
                    'id' => $item->id, 'product_name' => $item->product_name_snapshot,
                    'variant_label' => $item->variant_label_snapshot, 'quantity' => $item->quantity,
                    'unit_price_yen' => $item->unit_price_yen, 'discount_bps' => $item->discount_bps,
                    'discount_yen' => $item->discount_yen, 'line_total_yen' => $item->line_total_yen,
                    'image_url' => $item->image_url_snapshot,
                ]),
            ]),
        ];
    }

    /** @return array<string, mixed> */
    private function summary(Order $order): array
    {
        $order->loadMissing('producerOrders.producer.producerProfile', 'producerOrders.items');
        $this->completeExpiredOrder($order);
        $producerOrder = $order->producerOrders->first();

        return [
            'id' => $order->id, 'order_number' => $order->order_number,
            'placed_at' => $order->placed_at, 'order_state' => $order->order_state,
            'cancellation_deadline_at' => $order->cancellation_deadline_at,
            'payment_state' => $order->payment_state->value, 'refund_state' => $order->refund_state->value,
            'total_yen' => $order->total_yen,
            'shop_name' => $producerOrder?->shop_name_snapshot
                ?? $producerOrder?->producer->producerProfile?->farm_name,
            'items' => $producerOrder?->items->map(fn (OrderItem $item) => [
                'product_name' => $item->product_name_snapshot, 'quantity' => $item->quantity,
                'image_url' => $item->image_url_snapshot,
            ])->values() ?? [],
        ];
    }

    private function completeExpiredOrder(Order $order): void
    {
        if ($order->payment_state === PaymentState::Succeeded
            && $order->order_state === '注文確定'
            && now()->greaterThanOrEqualTo($order->cancellation_deadline_at)) {
            $order->update(['order_state' => '完了']);
        }
    }
}
