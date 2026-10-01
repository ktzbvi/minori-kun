<?php

namespace App\Http\Resources;

use App\Enums\FulfillmentState;
use App\Models\ProducerOrder;
use App\Models\ProducerSettlement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProducerDashboardResource extends JsonResource
{
    /**
     * @return array{
     *     products: array{total:int,published:int},
     *     orders: array{requiring_action:int,received:int,processing:int},
     *     sales: array{period_label:string,total_yen:int},
     *     payout_alert: ?array{expected_payout_yen:int,due_on:?string,state:string},
     *     action_orders: array<int,array{id:string,display_id:string,ordered_at:?string,product_summary:string,fulfillment_state:string}>
     * }
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $dashboard */
        $dashboard = $this->resource;
        $received = (int) $dashboard['orders_received'];
        $processing = (int) $dashboard['orders_processing'];
        $requiringAction = $received + $processing;
        $payoutSettlement = $dashboard['payout_settlement'];

        return [
            'products' => [
                'total' => (int) $dashboard['products_total'],
                'published' => (int) $dashboard['products_published'],
            ],
            'orders' => [
                'requiring_action' => $requiringAction,
                'received' => $received,
                'processing' => $processing,
            ],
            'sales' => [
                'period_label' => (string) $dashboard['period_label'],
                'total_yen' => (int) $dashboard['period_sales_yen'],
            ],
            'payout_alert' => $payoutSettlement instanceof ProducerSettlement
                ? $this->payoutAlert($payoutSettlement)
                : null,
            'action_orders' => $this->actionOrders($dashboard['action_orders']),
        ];
    }

    /** @return array{expected_payout_yen:int,due_on:?string,state:string} */
    private function payoutAlert(ProducerSettlement $settlement): array
    {
        return [
            'expected_payout_yen' => (int) ($settlement->payout?->expected_amount_yen ?? $settlement->expected_payout_yen),
            'due_on' => $settlement->payout?->due_on?->toDateString(),
            'state' => (string) ($settlement->payout?->state->value ?? $settlement->state->value),
        ];
    }

    /**
     * @param  Collection<int, ProducerOrder>  $orders
     * @return array<int,array{id:string,display_id:string,ordered_at:?string,product_summary:string,fulfillment_state:string}>
     */
    private function actionOrders(Collection $orders): array
    {
        return $orders->map(fn (ProducerOrder $order): array => [
            'id' => (string) $order->id,
            'display_id' => (string) $order->sub_order_number,
            'ordered_at' => $order->order?->placed_at?->toIso8601String(),
            'product_summary' => $this->productSummary($order),
            'fulfillment_state' => $order->fulfillment_state->value,
        ])->values()->all();
    }

    private function productSummary(ProducerOrder $order): string
    {
        $items = $order->items;
        $firstName = (string) ($items->first()?->product_name_snapshot ?? '商品未設定');
        $additionalCount = max(0, $items->count() - 1);

        return $additionalCount > 0 ? "{$firstName} ほか{$additionalCount}点" : $firstName;
    }
}
