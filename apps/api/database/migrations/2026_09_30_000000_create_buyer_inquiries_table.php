<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_inquiries', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference_number', 32)->unique();
            $table->uuid('idempotency_key');
            $table->string('subject', 255);
            $table->text('message');
            $table->timestampTz('submitted_at');
            $table->timestampsTz();
            $table->unique(['buyer_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_inquiries');
    }
};
