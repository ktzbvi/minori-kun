<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payjp_tenants', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('provider_tenant_reference')->unique();
            $table->string('application_state', 32)->default('not_applied')->index();
            $table->string('bank_state', 32)->default('unknown')->index();
            $table->timestampTz('last_authoritative_sync_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('payjp_screenings', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('payjp_tenants')->cascadeOnDelete();
            $table->string('card_brand', 24);
            $table->string('state', 24)->default('in_review')->index();
            $table->date('available_on')->nullable();
            $table->timestampTz('provider_updated_at')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'card_brand']);
        });

        Schema::create('producer_onboarding_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_id')->constrained('users')->restrictOnDelete();
            $table->string('event_type', 64)->index();
            $table->json('safe_metadata')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producer_onboarding_events');
        Schema::dropIfExists('payjp_screenings');
        Schema::dropIfExists('payjp_tenants');
    }
};
