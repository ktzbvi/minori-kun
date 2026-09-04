<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_order_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('payjp_tenant_id')->constrained('payjp_tenants')->restrictOnDelete();
            $table->string('provider_payment_reference')->nullable()->unique();
            $table->string('idempotency_reference')->unique();
            $table->unsignedBigInteger('amount_yen');
            $table->string('three_d_secure_state', 32)->default('not_started')->index();
            $table->string('payment_state', 32)->default('pending')->index();
            $table->timestampTz('browser_returned_at')->nullable();
            $table->timestampTz('authoritative_updated_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('refunds', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('payment_attempt_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('amount_yen');
            $table->string('reason', 64);
            $table->string('state', 32)->index();
            $table->string('provider_refund_reference')->nullable()->unique();
            $table->string('idempotency_reference')->unique();
            $table->timestampTz('authoritative_updated_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('payment_disputes', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('payment_attempt_id')->constrained()->restrictOnDelete();
            $table->string('provider_dispute_reference')->unique();
            $table->string('state', 32)->index();
            $table->unsignedBigInteger('amount_yen');
            $table->timestampTz('provider_created_at');
            $table->timestampTz('provider_updated_at');
            $table->timestampsTz();
        });

        Schema::create('provider_webhook_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('provider', 32);
            $table->string('provider_event_reference')->unique();
            $table->string('event_type', 96)->index();
            $table->char('payload_hash', 64);
            $table->string('processing_state', 32)->default('received')->index();
            $table->text('safe_error')->nullable();
            $table->timestampTz('provider_created_at')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('operation_scope', 96);
            $table->string('idempotency_key');
            $table->char('request_fingerprint', 64);
            $table->string('state', 24)->index();
            $table->string('result_type', 64)->nullable();
            $table->ulid('result_id')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();
            $table->unique(['operation_scope', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('provider_webhook_events');
        Schema::dropIfExists('payment_disputes');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_attempts');
    }
};
