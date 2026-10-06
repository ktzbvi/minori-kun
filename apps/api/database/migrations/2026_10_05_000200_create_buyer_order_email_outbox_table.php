<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_order_email_outboxes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('refund_id')->constrained()->restrictOnDelete();
            $table->string('event_type', 40);
            $table->string('deduplication_key', 120)->unique();
            $table->string('recipient_email');
            $table->json('payload');
            $table->string('state', 24)->default('queued')->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestampTz('locked_until')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->timestampsTz();
            $table->index(['state', 'locked_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_order_email_outboxes');
    }
};
