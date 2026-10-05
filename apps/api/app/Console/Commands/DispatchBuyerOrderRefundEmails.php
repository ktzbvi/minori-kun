<?php

namespace App\Console\Commands;

use App\Services\Buyer\BuyerOrderEmailOutboxDispatcher;
use Illuminate\Console\Command;

class DispatchBuyerOrderRefundEmails extends Command
{
    protected $signature = 'buyer:dispatch-refund-email-outbox';

    protected $description = 'Dispatch pending Buyer cancellation and refund emails';

    public function handle(BuyerOrderEmailOutboxDispatcher $dispatcher): int
    {
        $dispatcher->dispatchPending();

        return self::SUCCESS;
    }
}
