<?php

namespace App\Services\Buyer;

use App\Jobs\SendBuyerOrderRefundEmail;
use App\Models\BuyerOrderEmailOutbox;
use Illuminate\Support\Facades\DB;

class BuyerOrderEmailOutboxDispatcher
{
    public function dispatch(string $outboxId): void
    {
        $claimed = DB::transaction(function () use ($outboxId): bool {
            $row = BuyerOrderEmailOutbox::query()->lockForUpdate()->findOrFail($outboxId);
            if ($row->state === 'sent' || ($row->locked_until && $row->locked_until->isFuture())) {
                return false;
            }

            $row->update(['state' => 'dispatching', 'locked_until' => now()->addMinutes(5)]);

            return true;
        });

        if ($claimed) {
            SendBuyerOrderRefundEmail::dispatch($outboxId);
        }
    }

    public function dispatchPending(): void
    {
        BuyerOrderEmailOutbox::query()
            ->whereNull('sent_at')
            ->where(fn ($query) => $query->whereNull('locked_until')->orWhere('locked_until', '<=', now()))
            ->orderBy('created_at')
            ->limit(500)
            ->pluck('id')
            ->each(fn (string $id) => $this->dispatch($id));
    }
}
