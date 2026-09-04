<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('role', 24)->index();
            $table->string('email')->unique();
            $table->string('pending_email')->nullable();
            $table->string('password');
            $table->string('account_state', 24)->default('active')->index();
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('password_changed_at')->nullable();
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('buyer_profiles', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_phonetic')->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestampsTz();
        });

        Schema::create('buyer_addresses', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('phone', 32);
            $table->string('postal_code', 16);
            $table->string('prefecture', 64);
            $table->string('city');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();
            $table->index(['buyer_id', 'is_default']);
        });

        Schema::create('producer_profiles', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('farm_name');
            $table->string('representative_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('operational_state', 32)->default('onboarding')->index();
            $table->timestampTz('selling_eligible_at')->nullable();
            $table->timestampTz('selling_suspended_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('producer_terms_acceptances', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_id')->constrained('users')->cascadeOnDelete();
            $table->string('terms_version', 64);
            $table->timestampTz('accepted_at');
            $table->timestampsTz();
            $table->unique(['producer_id', 'terms_version']);
        });

        Schema::create('email_verification_tokens', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 32);
            $table->char('token_hash', 64)->unique();
            $table->timestampTz('expires_at');
            $table->timestampTz('consumed_at')->nullable();
            $table->timestampTz('invalidated_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestampTz('expires_at');
            $table->timestampTz('consumed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('email_verification_tokens');
        Schema::dropIfExists('producer_terms_acceptances');
        Schema::dropIfExists('producer_profiles');
        Schema::dropIfExists('buyer_addresses');
        Schema::dropIfExists('buyer_profiles');
        Schema::dropIfExists('users');
    }
};
