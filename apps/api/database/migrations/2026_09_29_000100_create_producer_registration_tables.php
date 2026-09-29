<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producer_registration_attempts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('purpose', 32)->default('producer_registration');
            $table->char('session_token_hash', 64)->unique();
            $table->string('email')->nullable()->index();
            $table->char('otp_hmac', 64)->nullable();
            $table->unsignedInteger('otp_generation')->default(0);
            $table->unsignedInteger('verification_attempts')->default(0);
            $table->unsignedInteger('resend_count')->default(0);
            $table->timestampTz('otp_expires_at')->nullable();
            $table->boolean('delivery_succeeded')->nullable();
            $table->timestampTz('otp_delivered_at')->nullable();
            $table->timestampTz('otp_consumed_at')->nullable();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('grant_expires_at')->nullable();
            $table->timestampTz('expires_at');
            $table->timestampTz('consumed_at')->nullable();
            $table->foreignUlid('resulting_producer_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestampTz('invalidated_at')->nullable();
            $table->ulid('current_photo_id')->nullable()->index();
            $table->timestampsTz();
            $table->index(['verified_at', 'grant_expires_at'], 'prod_reg_attempts_verified_grant_idx');
            $table->index(['consumed_at', 'expires_at'], 'prod_reg_attempts_consumed_expires_idx');
        });

        Schema::create('producer_registration_email_cooldowns', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->timestampTz('resend_available_at');
            $table->timestampTz('last_requested_at');
            $table->timestampsTz();
        });

        Schema::create('producer_registration_photos', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('attempt_id')->nullable()->constrained('producer_registration_attempts')->nullOnDelete();
            $table->string('storage_path')->nullable();
            $table->string('mime_type', 32);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedInteger('size_bytes');
            $table->foreignUlid('claimed_by_producer_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $table->timestampTz('claimed_at')->nullable();
            $table->timestampTz('expires_at');
            $table->timestampTz('deleted_at')->nullable();
            $table->timestampsTz();
            $table->index(['attempt_id', 'claimed_at', 'expires_at'], 'prod_reg_photos_attempt_claimed_expires_idx');
        });

        Schema::table('producer_profiles', function (Blueprint $table): void {
            $table->foreignUlid('shop_photo_id')->nullable()->unique()->constrained('producer_registration_photos')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('producer_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('shop_photo_id');
        });
        Schema::dropIfExists('producer_registration_photos');
        Schema::dropIfExists('producer_registration_email_cooldowns');
        Schema::dropIfExists('producer_registration_attempts');
    }
};
