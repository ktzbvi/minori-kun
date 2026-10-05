<?php

namespace App\Services\Buyer;

use App\Enums\PaymentState;
use App\Enums\RefundState;
use App\Models\BuyerOrderEmailOutbox;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyerOrderRefundStatusService
{
    public function apply(string $refundId, string $providerStatus, ?\DateTimeInterface $updatedAt = null): Refund
    {
        $targetState = $this->mapProviderState($providerStatus);

        [$refund, $outboxIds, $failed] = DB::transaction(function () use ($refundId, $targetState, $updatedAt): array {
            $refund = Refund::query()
                ->where('id', $refundId)
                ->orWhere('provider_refund_reference', $refundId)
                ->lockForUpdate()
                ->firstOrFail();
            $current = $refund->state;
            if ($updatedAt && $refund->authoritative_updated_at && $updatedAt < $refund->authoritative_updated_at) {
                return [$refund, [], false];
            }

            if ($current === RefundState::Refunded || ($current === RefundState::Failed && $targetState !== RefundState::Refunded)) {
                return [$refund, [], false];
            }

            $transitionedToSuccess = $targetState === RefundState::Refunded && $current !== RefundState::Refunded;
            $refund->update([
                'state' => $targetState,
                'authoritative_updated_at' => $updatedAt ?? now(),
            ]);

            $attempt = PaymentAttempt::query()->with('producerOrder.order.buyer')
                ->lockForUpdate()->findOrFail($refund->payment_attempt_id);
            $order = Order::query()->lockForUpdate()->findOrFail($attempt->producerOrder->order_id);
            $order->update(['refund_state' => $targetState]);

            if ($targetState === RefundState::Refunded) {
                $attempt->update(['payment_state' => PaymentState::Refunded, 'authoritative_updated_at' => $updatedAt ?? now()]);
                $order->update(['payment_state' => PaymentState::Refunded]);
            }

            $outboxIds = [];
            if ($transitionedToSuccess) {
                $outboxIds[] = $this->recordEmail($order, $refund, 'refund_completed')->id;
            }

            return [$refund->fresh(), $outboxIds, in_array($targetState, [RefundState::Failed, RefundState::Canceled], true)];
        }, 3);

        foreach ($outboxIds as $outboxId) {
            app(BuyerOrderEmailOutboxDispatcher::class)->dispatch($outboxId);
        }

        if ($failed) {
            Log::critical('Buyer order refund requires manual follow-up.', [
                'order_id' => $refund->paymentAttempt->producerOrder->order_id,
                'refund_id' => $refund->id,
                'provider_refund_reference' => $refund->provider_refund_reference,
                'state' => $refund->state->value,
            ]);
        }

        return $refund;
    }

    public function mapProviderState(string $status): RefundState
    {
        return match ($status) {
            'succeeded', 'refunded' => RefundState::Refunded,
            'pending' => RefundState::Pending,
            'requires_action' => RefundState::RequiresAction,
            'failed' => RefundState::Failed,
            'canceled', 'cancelled' => RefundState::Canceled,
            default => throw new \InvalidArgumentException('Unknown provider refund status.'),
        };
    }

    public function recordEmail(Order $order, Refund $refund, string $eventType): BuyerOrderEmailOutbox
    {
        $buyer = $order->buyer()->firstOrFail();
        $producerOrder = $refund->paymentAttempt->producerOrder;
        $key = $order->id.':'.$eventType;

        return BuyerOrderEmailOutbox::query()->firstOrCreate(['deduplication_key' => $key], [
            'order_id' => $order->id,
            'refund_id' => $refund->id,
            'event_type' => $eventType,
            'recipient_email' => $buyer->email,
            'payload' => [
                'orderNumber' => $order->order_number,
                'shopName' => $producerOrder->shop_name_snapshot ?? 'ショップ',
                'refundAmountYen' => (int) $refund->amount_yen,
            ],
        ]);
    }
}
