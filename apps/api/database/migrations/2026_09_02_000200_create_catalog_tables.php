<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestampsTz();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('producer_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('type_name')->nullable();
            $table->string('publication_state', 24)->default('draft')->index();
            $table->string('moderation_state', 24)->default('clear')->index();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->index(['producer_id', 'publication_state']);
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 64);
            $table->string('object_path')->unique();
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('display_order');
            $table->timestampsTz();
            $table->unique(['product_id', 'display_order']);
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('option_label');
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('price_yen');
            $table->unsignedInteger('stock_quantity');
            $table->unsignedSmallInteger('discount_bps')->default(0);
            $table->unsignedInteger('display_order')->default(0);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestampsTz();
            $table->unique(['product_id', 'option_label']);
            $table->unique(['product_id', 'display_order']);
        });

        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->integer('quantity_delta');
            $table->string('reason', 48);
            $table->string('source_type', 64)->nullable();
            $table->ulid('source_id')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('stock_reservations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('state', 24)->default('active')->index();
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('released_at')->nullable();
            $table->timestampTz('converted_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('product_moderation_events', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->restrictOnDelete();
            $table->foreignUlid('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 48);
            $table->text('safe_reason')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('banner_settings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('singleton_id')->primary()->default(1);
            $table->boolean('is_visible')->default(false);
            $table->string('image_disk', 64)->nullable();
            $table->string('image_path')->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestampsTz();
        });

        Schema::create('daily_recommendations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->date('recommendation_date')->unique();
            $table->timestampTz('generated_at');
            $table->timestampsTz();
        });

        Schema::create('daily_recommendation_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('recommendation_id')->constrained('daily_recommendations')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('display_order');
            $table->unique(['recommendation_id', 'product_id'], 'daily_rec_product_unique');
            $table->unique(['recommendation_id', 'display_order'], 'daily_rec_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_recommendation_items');
        Schema::dropIfExists('daily_recommendations');
        Schema::dropIfExists('banner_settings');
        Schema::dropIfExists('product_moderation_events');
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
