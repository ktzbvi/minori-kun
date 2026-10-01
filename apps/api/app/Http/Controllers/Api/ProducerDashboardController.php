<?php

namespace App\Http\Controllers\Api;

use App\Enums\FulfillmentState;
use App\Enums\PaymentState;
use App\Enums\PayoutState;
use App\Enums\ProductPublicationState;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProducerDashboardResource;
use App\Models\ProducerOrder;
use App\Models\ProducerSettlement;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ProducerDashboardController extends Controller
{
    public function show(Request $request): ProducerDashboardResource
    {
        $producerId = (string) $request->user()->id;
        $periodStart = CarbonImmutable::now('Asia/Tokyo')->startOfMonth();
        $periodEnd = $periodStart->endOfMonth();

        $productQuery = Product::query()->where('producer_id', $producerId);
        $orderQuery = ProducerOrder::query()->where('producer_id', $producerId);

        $actionStates = [
            FulfillmentState::Received->value,
            FulfillmentState::Processing->value,
        ];

        $productSummary = (clone $productQuery)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw(
                'SUM(CASE WHEN publication_state = ? THEN 1 ELSE 0 END) as published_count',
                [ProductPublicationState::Published->value],
            )
            ->first();

        $orderSummary = (clone $orderQuery)
            ->leftJoin('orders', 'orders.id', '=', 'producer_orders.order_id')
            ->selectRaw(
                'SUM(CASE WHEN producer_orders.fulfillment_state = ? THEN 1 ELSE 0 END) as received_count',
                [FulfillmentState::Received->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN producer_orders.fulfillment_state = ? THEN 1 ELSE 0 END) as processing_count',
                [FulfillmentState::Processing->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN orders.payment_state = ? AND orders.placed_at BETWEEN ? AND ? THEN producer_orders.total_yen ELSE 0 END) as period_sales_yen',
                [PaymentState::Succeeded->value, $periodStart, $periodEnd],
            )
            ->first();

        $actionOrders = (clone $orderQuery)
            ->whereIn('fulfillment_state', $actionStates)
            ->with([
                'order:id,placed_at,order_number',
                'items:id,producer_order_id,product_name_snapshot,quantity',
            ])
            ->latest('updated_at')
            ->limit(3)
            ->get();

        $payoutSettlement = ProducerSettlement::query()
            ->where('producer_id', $producerId)
            ->with('payout')
            ->whereHas('payout', fn ($query) => $query->where('state', '!=', PayoutState::Paid->value))
            ->latest('period_end')
            ->first();

        return new ProducerDashboardResource([
            'products_total' => (int) ($productSummary?->total_count ?? 0),
            'products_published' => (int) ($productSummary?->published_count ?? 0),
            'orders_received' => (int) ($orderSummary?->received_count ?? 0),
            'orders_processing' => (int) ($orderSummary?->processing_count ?? 0),
            'period_label' => $periodStart->format('Y年n月'),
            'period_sales_yen' => (int) ($orderSummary?->period_sales_yen ?? 0),
            'action_orders' => $actionOrders,
            'payout_settlement' => $payoutSettlement,
        ]);
    }
}
