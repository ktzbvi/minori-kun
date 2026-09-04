<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producer_settlements', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_id')->constrained('users')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('state', 24)->default('provisional')->index();
            $table->unsignedBigInteger('expected_payout_yen')->default(0);
            $table->timestampTz('finalized_at')->nullable();
            $table->timestampsTz();
            $table->unique(['producer_id', 'period_start', 'period_end']);
        });

        Schema::create('settlement_lines', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('settlement_id')->constrained('producer_settlements')->restrictOnDelete();
            $table->string('component', 48);
            $table->bigInteger('amount_yen');
            $table->string('source_type', 64)->nullable();
            $table->ulid('source_id')->nullable();
            $table->json('safe_metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['settlement_id', 'component']);
        });

        Schema::create('payouts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('settlement_id')->unique()->constrained('producer_settlements')->restrictOnDelete();
            $table->unsignedBigInteger('expected_amount_yen');
            $table->unsignedBigInteger('provider_amount_yen')->nullable();
            $table->unsignedBigInteger('carry_forward_yen')->default(0);
            $table->date('due_on')->nullable();
            $table->date('paid_on')->nullable();
            $table->string('state', 32)->index();
            $table->string('provider_payout_reference')->nullable()->unique();
            $table->timestampsTz();
        });

        Schema::create('payout_documents', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('payout_id')->constrained()->restrictOnDelete();
            $table->string('document_type', 32);
            $table->string('provider_document_reference');
            $table->timestampTz('available_at')->nullable();
            $table->timestampsTz();
            $table->unique(['payout_id', 'document_type']);
        });

        Schema::create('producer_bank_snapshots', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_id')->constrained('users')->restrictOnDelete();
            $table->string('bank_name_masked')->nullable();
            $table->string('branch_name_masked')->nullable();
            $table->string('account_number_masked')->nullable();
            $table->string('account_holder_masked')->nullable();
            $table->string('provider_state', 32)->index();
            $table->timestampTz('provider_updated_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('payout_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('payout_id')->constrained()->restrictOnDelete();
            $table->string('from_state', 32)->nullable();
            $table->string('to_state', 32);
            $table->json('safe_metadata')->nullable();
            $table->timestampTz('provider_occurred_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_events');
        Schema::dropIfExists('producer_bank_snapshots');
        Schema::dropIfExists('payout_documents');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('settlement_lines');
        Schema::dropIfExists('producer_settlements');
    }
};
