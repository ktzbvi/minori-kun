<?php

namespace App\Http\Resources;

use App\Enums\FulfillmentState;
use App\Enums\PaymentState;
use App\Enums\RefundState;
use App\Models\OrderItem;
use App\Models\ProducerOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProducerOrder */
class ProducerOrderListItemResource extends JsonResource
{
    /** @return array{id:string,display_id:string,ordered_at:?string,fulfillment_state:'received'|'processing'|'shipped',operational_state:'received'|'confirmed'|'processing'|'shipped'|'cancelled',status_owner:'producer'|'system',order_state:string,refund_state:'none'|'pending'|'partial'|'refunded'|'failed',items:array<int,array{id:string,product_name:string,quantity:int}>} */
    public function toArray(Request $request): array
    {
        $order = $this->order;
        $state = $this->fulfillment_state->value;
        $systemControlled = $order->refund_state !== RefundState::None || $order->payment_state === PaymentState::Refunded;
        if ($order->order_state === 'cancelled') {
            $state = 'cancelled';
            $systemControlled = true;
        } elseif ($this->fulfillment_state === FulfillmentState::Received
            && $order->payment_state === PaymentState::Succeeded
            && $order->refund_state === RefundState::None
            && $order->cancellation_deadline_at?->lte(now())) {
            // P08 presentation only; preserve the independent Buyer order and fulfillment states.
            $state = 'confirmed';
            $systemControlled = true;
        }

        return [
            'id' => (string) $this->id,
            'display_id' => (string) $this->sub_order_number,
            'ordered_at' => $order->placed_at?->toIso8601String(),
            'fulfillment_state' => $this->fulfillment_state->value,
            /** @var 'received'|'confirmed'|'processing'|'shipped'|'cancelled' */
            'operational_state' => $state,
            'status_owner' => $systemControlled ? 'system' : 'producer',
            'order_state' => (string) $order->order_state,
            'refund_state' => $order->refund_state->value,
            'items' => $this->items->map(static fn (OrderItem $item): array => [
                'id' => (string) $item->id,
                'product_name' => (string) $item->product_name_snapshot,
                'quantity' => (int) $item->quantity,
            ])->values()->all(),
        ];
    }
}
