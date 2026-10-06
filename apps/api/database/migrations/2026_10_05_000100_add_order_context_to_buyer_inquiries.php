<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyer_inquiries', function (Blueprint $table): void {
            $table->foreignUlid('producer_order_id')->nullable()->after('buyer_id')
                ->constrained('producer_orders')->nullOnDelete();
            $table->string('topic', 100)->nullable()->after('subject');
        });
    }

    public function down(): void
    {
        Schema::table('buyer_inquiries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('producer_order_id');
            $table->dropColumn('topic');
        });
    }
};
