<?php

namespace App\Domain\Orders;

use App\Enums\PaymentState;
use App\Enums\RefundState;
use App\Models\Order;
use App\Models\ProducerOrder;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListProducerOrders
{
    // FR-P-008 / SEC-002: every list, search, and item lookup is Producer-scoped.
    public function query(string $producerId): Builder
    {
        return ProducerOrder::query()
            ->where('producer_id', $producerId)
            ->whereHas('order', fn (Builder $query) => $query->whereIn('payment_state', [
                PaymentState::Succeeded->value, PaymentState::Refunded->value,
            ]))
            ->with([
                'order:id,placed_at,order_number,order_state,payment_state,refund_state,cancellation_deadline_at',
                'items' => fn (HasMany $query) => $query->where('producer_id', $producerId)
                    ->select('id', 'producer_order_id', 'product_name_snapshot', 'quantity')
                    ->orderBy('id'),
            ]);
    }

    public function handle(string $producerId, array $filters): LengthAwarePaginator
    {
        $query = $this->query($producerId);
        $keyword = ltrim($filters['keyword'] ?? '', '#');
        if ($keyword !== '') {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $keyword).'%';
            $query->where(function (Builder $query) use ($like, $producerId): void {
                $query->whereRaw("sub_order_number LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("legacy_sub_order_number LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('order', fn (Builder $query) => $query->whereRaw("order_number LIKE ? ESCAPE '!'", [$like])->orWhereRaw("legacy_order_number LIKE ? ESCAPE '!'", [$like]))
                    ->orWhereHas('items', fn (Builder $query) => $query->where('producer_id', $producerId)
                        ->whereRaw("product_name_snapshot LIKE ? ESCAPE '!'", [$like]));
            });
        }

        if (($filters['fulfillment_state'] ?? 'all') !== 'all') {
            $query->where('fulfillment_state', $filters['fulfillment_state']);
        }

        $status = $filters['status'] ?? 'all';
        if ($status === 'cancelled') {
            $query->whereHas('order', fn (Builder $query) => $query->where('order_state', 'cancelled'));
        } elseif ($status === 'refunded') {
            $query->whereHas('order', fn (Builder $query) => $query->where('refund_state', RefundState::Refunded->value));
        } elseif ($status !== 'all') {
            $query->where('fulfillment_state', $status)
                ->whereHas('order', fn (Builder $query) => $query->where('order_state', '!=', 'cancelled')
                    ->where('refund_state', '!=', RefundState::Refunded->value));
        }

        $now = CarbonImmutable::now('Asia/Tokyo');
        $period = $filters['period'] ?? 'all';
        [$start, $end] = match ($period) {
            '30d' => [$now->subDays(30), $now],
            '90d' => [$now->subDays(90), $now],
            '12m' => [$now->subMonthsNoOverflow(12), $now],
            'year' => [
                CarbonImmutable::create((int) $filters['year'], 1, 1, 0, 0, 0, 'Asia/Tokyo'),
                CarbonImmutable::create((int) $filters['year'] + 1, 1, 1, 0, 0, 0, 'Asia/Tokyo'),
            ],
            'custom' => [
                CarbonImmutable::parse($filters['from'], 'Asia/Tokyo')->startOfDay(),
                CarbonImmutable::parse($filters['to'], 'Asia/Tokyo')->startOfDay()->addDay(),
            ],
            default => [null, null],
        };
        if ($start !== null) {
            $query->whereHas('order', function (Builder $query) use ($start, $end, $period): void {
                $query->where('placed_at', '>=', $start->utc())
                    ->where('placed_at', in_array($period, ['year', 'custom'], true) ? '<' : '<=', $end->utc());
            });
        }

        return $query->orderByDesc(
            Order::query()->select('placed_at')->whereColumn('orders.id', 'producer_orders.order_id'),
        )->orderByDesc('id')->paginate(20)->withQueryString();
    }
}
