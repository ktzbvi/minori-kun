<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_registration_attempts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('email')->unique();
            $table->string('otp_hash');
            $table->timestampTz('otp_sent_at');
            $table->timestampTz('otp_expires_at');
            $table->timestampTz('resend_available_at');
            $table->timestampTz('registration_session_expires_at');
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('consumed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_registration_attempts');
    }
};
