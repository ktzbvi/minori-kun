<?php

namespace App\Http\Resources;

use App\Enums\PaymentState;
use App\Enums\RefundState;
use App\Models\ProducerOrder;
use Illuminate\Http\Request;

/** @mixin ProducerOrder */
class ProducerOrderDetailResource extends ProducerOrderListItemResource
{
    /** @return array{id:string,display_id:string,ordered_at:?string,fulfillment_state:'received'|'processing'|'shipped',operational_state:'received'|'confirmed'|'processing'|'shipped'|'cancelled',status_owner:'producer'|'system',order_state:string,refund_state:string,can_update_fulfillment:bool,items:array<int,array{id:string,product_name:string,image_url:?string,quantity:int,unit_price_yen:int,discount_bps:int,line_total_yen:int}>,delivery_address:?array{recipient_name:string,postal_code:string,address:string,address_line2:?string,phone:string}} */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['can_update_fulfillment'] = $this->order->order_state !== 'cancelled'
            && $this->order->payment_state === PaymentState::Succeeded
            && $this->order->refund_state === RefundState::None;
        $items = $this->items->map(fn ($item): array => [
            'id' => (string) $item->id,
            'product_name' => (string) $item->product_name_snapshot,
            'image_url' => $item->image_url_snapshot,
            'quantity' => (int) $item->quantity,
            'unit_price_yen' => (int) $item->unit_price_yen,
            'discount_bps' => (int) $item->discount_bps,
            'line_total_yen' => (int) $item->line_total_yen,
        ])->values()->all();
        $address = $this->order->deliveryAddress;
        $deliveryAddress = $address ? [
            'recipient_name' => (string) $address->recipient_name,
            'postal_code' => (string) $address->postal_code,
            'address' => $address->prefecture.$address->city.$address->address_line1,
            'address_line2' => $address->address_line2,
            'phone' => (string) $address->phone,
        ] : null;

        return [
            'id' => (string) $this->id,
            'display_id' => (string) $this->sub_order_number,
            'ordered_at' => $this->order->placed_at?->toIso8601String(),
            /** @var 'received'|'processing'|'shipped' */
            'fulfillment_state' => $this->fulfillment_state->value,
            /** @var 'received'|'confirmed'|'processing'|'shipped'|'cancelled' */
            'operational_state' => $data['operational_state'],
            /** @var 'producer'|'system' */
            'status_owner' => $data['status_owner'],
            'order_state' => (string) $this->order->order_state,
            /** @var 'none'|'pending'|'partial'|'refunded'|'failed'|'requires_action'|'canceled' */
            'refund_state' => $this->order->refund_state->value,
            'items' => $items,
            /** @var bool */
            'can_update_fulfillment' => $data['can_update_fulfillment'],
            'delivery_address' => $deliveryAddress,
        ];
    }
}
