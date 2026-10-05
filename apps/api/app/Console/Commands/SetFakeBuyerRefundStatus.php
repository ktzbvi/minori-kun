<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Refund;
use App\Services\Buyer\BuyerOrderRefundStatusService;
use Illuminate\Console\Command;

class SetFakeBuyerRefundStatus extends Command
{
    protected $signature = 'buyer:fake-refund-status {order} {status}';

    protected $description = 'Simulate an authoritative refund status in local or test environments';

    public function handle(BuyerOrderRefundStatusService $service): int
    {
        if (! app()->environment(['local', 'testing']) || config('buyer_payment.driver') !== 'fake') {
            $this->error('Fake refunds are only available with the fake payment driver in local/testing.');

            return self::FAILURE;
        }

        $order = Order::query()->whereKey($this->argument('order'))
            ->orWhere('order_number', $this->argument('order'))
            ->first();
        $refund = $order
            ? Refund::query()->whereHas('paymentAttempt.producerOrder', fn ($query) => $query->where('order_id', $order->id))->first()
            : null;
        if (! $refund) {
            $this->error('Refund not found.');

            return self::FAILURE;
        }

        try {
            $updated = $service->apply($refund->provider_refund_reference, $this->argument('status'));
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Refund {$updated->id}: {$updated->state->value}");

        return self::SUCCESS;
    }
}
