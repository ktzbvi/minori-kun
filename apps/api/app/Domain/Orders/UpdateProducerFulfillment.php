<?php

namespace App\Domain\Orders;

use App\Enums\PaymentState;
use App\Enums\RefundState;
use App\Models\AuditEvent;
use App\Models\Order;
use App\Models\ProducerOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateProducerFulfillment
{
    // FR-P-009 / AT-P-009: lock order first, matching cancellation's serialization boundary.
    public function handle(User $actor, string $id, array $input): void
    {
        DB::transaction(function () use ($actor, $id, $input): void {
            $owned = ProducerOrder::query()->where('producer_id', $actor->id)->findOrFail($id);
            $order = Order::query()->lockForUpdate()->findOrFail($owned->order_id);
            $record = ProducerOrder::query()->lockForUpdate()->findOrFail($id);
            Gate::authorize('updateFulfillment', $record);
            abort_if($order->order_state === 'cancelled' || $order->payment_state !== PaymentState::Succeeded
                || $order->refund_state !== RefundState::None, 409, 'この注文の配送対応状況は更新できません。');
            $before = $record->fulfillment_state->value;
            // Repeated commands are successful no-ops; stale different commands cannot overwrite newer state.
            if ($before === $input['fulfillment_state']) {
                return;
            }
            abort_if($before !== $input['expected_state'], 409, '状態が変更されています。再読み込みしてください。');
            $record->update(['fulfillment_state' => $input['fulfillment_state']]);
            AuditEvent::query()->create([
                'actor_id' => $actor->id, 'actor_role' => 'producer',
                'target_type' => 'producer_order', 'target_id' => $record->id,
                'action' => 'producer.fulfillment.updated',
                'safe_before' => json_encode(['fulfillment_state' => $before], JSON_THROW_ON_ERROR),
                'safe_after' => json_encode(['fulfillment_state' => $input['fulfillment_state']], JSON_THROW_ON_ERROR),
                'result' => 'success', 'occurred_at' => now(),
            ]);
        });
    }
}
