<?php

use App\Models\AuditEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // DATA-006 / P08-01: change only the legacy sample fixture references.
        DB::transaction(function (): void {
            $records = DB::table('producer_orders')->join('orders', 'orders.id', '=', 'producer_orders.order_id')
                ->where('orders.order_number', 'like', 'SAMPLE-%')
                ->select('producer_orders.id', 'producer_orders.producer_id', 'orders.id as order_id', 'orders.order_number')
                ->get();
            foreach ($records as $record) {
                $prefix = 'SAMPLE-'.$record->producer_id.'-';
                if (! str_starts_with($record->order_number, $prefix)) {
                    continue;
                }
                $number = 'SAMPLE-'.strtoupper(substr(hash('sha256', $record->producer_id), 0, 10)).'-'.substr($record->order_number, strlen($prefix));
                DB::table('orders')->where('id', $record->order_id)->update(['order_number' => $number]);
                DB::table('producer_orders')->where('id', $record->id)->update(['sub_order_number' => $number.'-01']);
                AuditEvent::query()->create([
                    'target_type' => 'order', 'target_id' => $record->order_id,
                    'action' => 'sample_order.reference_shortened', 'result' => 'success',
                    'reason' => 'Legacy sample reference replaced; order identity and financial state preserved.',
                    'occurred_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Preserve issued references on rollback; no financial or identity data changed.
    }
};
