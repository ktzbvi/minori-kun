<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('checkout_key', 100)->nullable()->unique();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->text('image_url_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('image_url_snapshot'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropUnique(['checkout_key'])->dropColumn('checkout_key'));
    }
};
