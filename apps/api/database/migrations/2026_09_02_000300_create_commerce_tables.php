<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('buyer_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->char('guest_reference_hash', 64)->nullable()->unique();
            $table->string('state', 24)->default('active')->index();
            $table->timestampTz('expires_at')->nullable()->index();
            $table->timestampsTz();
        });

        Schema::create('cart_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestampsTz();
            $table->unique(['cart_id', 'variant_id']);
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('buyer_id')->constrained('users')->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('order_state', 32)->index();
            $table->string('payment_state', 32)->index();
            $table->string('refund_state', 32)->index();
            $table->unsignedBigInteger('subtotal_yen');
            $table->unsignedBigInteger('discount_yen')->default(0);
            $table->unsignedBigInteger('shipping_yen')->default(0);
            $table->unsignedBigInteger('total_yen');
            $table->timestampTz('cancellation_deadline_at');
            $table->timestampTz('placed_at')->index();
            $table->timestampsTz();
        });

        Schema::create('order_delivery_addresses', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('phone', 32);
            $table->string('postal_code', 16);
            $table->string('prefecture', 64);
            $table->string('city');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('producer_orders', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('producer_id')->constrained('users')->restrictOnDelete();
            $table->string('sub_order_number')->unique();
            $table->string('fulfillment_state', 32)->default('received')->index();
            $table->unsignedBigInteger('subtotal_yen');
            $table->unsignedBigInteger('producer_discount_yen')->default(0);
            $table->unsignedSmallInteger('company_commission_bps')->default(1000);
            $table->unsignedBigInteger('company_commission_yen')->default(0);
            $table->unsignedBigInteger('total_yen');
            $table->timestampsTz();
            $table->unique(['order_id', 'producer_id']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_order_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignUlid('producer_id')->constrained('users')->restrictOnDelete();
            $table->string('product_name_snapshot');
            $table->string('variant_label_snapshot');
            $table->unsignedBigInteger('unit_price_yen');
            $table->unsignedSmallInteger('discount_bps');
            $table->unsignedBigInteger('discount_yen');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_yen');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('fulfillment_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_order_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_state', 32)->nullable();
            $table->string('to_state', 32);
            $table->timestampTz('occurred_at');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('order_cancellations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignUlid('requested_by')->constrained('users')->restrictOnDelete();
            $table->string('state', 32)->index();
            $table->string('idempotency_reference')->unique();
            $table->text('safe_reason')->nullable();
            $table->timestampTz('requested_at');
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_cancellations');
        Schema::dropIfExists('fulfillment_events');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('producer_orders');
        Schema::dropIfExists('order_delivery_addresses');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
