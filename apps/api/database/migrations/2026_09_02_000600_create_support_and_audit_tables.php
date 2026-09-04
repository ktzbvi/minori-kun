<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_number')->unique();
            $table->string('subject');
            $table->string('state', 24)->index();
            $table->timestampsTz();
        });

        Schema::create('inquiry_messages', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('inquiry_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_role', 24);
            $table->text('body');
            $table->json('safe_internal_metadata')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('audit_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 24)->nullable();
            $table->string('target_type', 96);
            $table->ulid('target_id')->nullable();
            $table->string('action', 96)->index();
            $table->json('safe_before')->nullable();
            $table->json('safe_after')->nullable();
            $table->string('result', 32);
            $table->text('reason')->nullable();
            $table->string('severity', 24)->default('info');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('occurred_at')->index();
            $table->timestampTz('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('inquiry_messages');
        Schema::dropIfExists('inquiries');
    }
};
