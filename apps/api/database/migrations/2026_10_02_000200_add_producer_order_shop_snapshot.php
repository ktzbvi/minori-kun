<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producer_orders', function (Blueprint $table): void {
            $table->string('shop_name_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('producer_orders', function (Blueprint $table): void {
            $table->dropColumn('shop_name_snapshot');
        });
    }
};
