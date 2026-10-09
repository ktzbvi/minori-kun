<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->index(['publication_state', 'deleted_at', 'created_at', 'id'], 'products_buyer_feed_index');
            $table->index(['category_id', 'publication_state', 'deleted_at', 'created_at', 'id'], 'products_buyer_category_feed_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_buyer_feed_index');
            $table->dropIndex('products_buyer_category_feed_index');
        });
    }
};
