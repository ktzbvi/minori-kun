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
            'products_total' => (clone $productQuery)->count(),
            'products_published' => (clone $productQuery)
                ->where('publication_state', ProductPublicationState::Published->value)
                ->count(),
            'orders_received' => (clone $orderQuery)
                ->where('fulfillment_state', FulfillmentState::Received->value)
                ->count(),
            'orders_processing' => (clone $orderQuery)
                ->where('fulfillment_state', FulfillmentState::Processing->value)
                ->count(),
            'period_label' => $periodStart->format('Y年n月'),
            'period_sales_yen' => (int) (clone $orderQuery)
                ->whereHas('order', fn ($query) => $query
                    ->where('payment_state', PaymentState::Succeeded->value)
                    ->whereBetween('placed_at', [$periodStart, $periodEnd]))
                ->sum('total_yen'),
            'action_orders' => $actionOrders,
            'payout_settlement' => $payoutSettlement,
        ]);
    }
}
