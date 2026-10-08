<?php

use App\Models\AuditEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_reference_sequences', function (Blueprint $table): void {
            $table->string('kind')->primary();
            $table->unsignedBigInteger('next_value');
        });
        Schema::table('orders', fn (Blueprint $table) => $table->string('legacy_order_number')->nullable()->index());
        Schema::table('producer_orders', fn (Blueprint $table) => $table->string('legacy_sub_order_number')->nullable()->index());
        // DATA-004/006: preserve internal IDs, historical search references and all financial state.
        DB::transaction(function (): void {
            $products = DB::table('products')->orderBy('id')->get(['id']);
            foreach ($products as $index => $product) {
                DB::table('products')->where('id', $product->id)->update([
                    'product_code' => 'P-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                ]);
            }
            $orders = DB::table('orders')->orderBy('id')->get(['id', 'order_number']);
            $records = DB::table('producer_orders')->get(['id', 'sub_order_number']);
            // Vacate the namespace before renumbering to avoid existing O-000001 collisions.
            foreach ($orders as $order) {
                DB::table('orders')->where('id', $order->id)->update([
                    'legacy_order_number' => $order->order_number, 'order_number' => 'MIGRATION-'.$order->id,
                ]);
            }
            foreach ($records as $record) {
                DB::table('producer_orders')->where('id', $record->id)->update([
                    'legacy_sub_order_number' => $record->sub_order_number, 'sub_order_number' => 'MIGRATION-'.$record->id,
                ]);
            }
            foreach ($orders as $index => $order) {
                $number = 'O-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT);
                DB::table('orders')->where('id', $order->id)->update(['order_number' => $number]);
                $ownedRecords = DB::table('producer_orders')->where('order_id', $order->id)->orderBy('id')->get(['id']);
                foreach ($ownedRecords as $itemIndex => $record) {
                    DB::table('producer_orders')->where('id', $record->id)->update([
                        'sub_order_number' => $ownedRecords->count() === 1 ? $number : $number.'-'.($itemIndex + 1),
                    ]);
                }
                AuditEvent::query()->create([
                    'target_type' => 'order', 'target_id' => $order->id,
                    'action' => 'order.reference_reformatted', 'result' => 'success',
                    'reason' => 'Sequential public reference assigned; previous reference retained for search.',
                    'occurred_at' => now(),
                ]);
            }
            DB::table('public_reference_sequences')->insert([
                ['kind' => 'product', 'next_value' => $products->count() + 1],
                ['kind' => 'order', 'next_value' => $orders->count() + 1],
            ]);
        });
    }

    public function down(): void
    {
        // Issued public references and their allocation counters must not be reused.
    }
};
