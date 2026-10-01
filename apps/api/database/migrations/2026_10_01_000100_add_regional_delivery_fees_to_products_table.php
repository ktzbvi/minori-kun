<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedBigInteger('delivery_fee_honshu_yen')->default(0)->after('description');
            $table->unsignedBigInteger('delivery_fee_hokkaido_yen')->default(0)->after('delivery_fee_honshu_yen');
            $table->unsignedBigInteger('delivery_fee_okinawa_yen')->default(0)->after('delivery_fee_hokkaido_yen');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'delivery_fee_honshu_yen',
                'delivery_fee_hokkaido_yen',
                'delivery_fee_okinawa_yen',
            ]);
        });
    }
};
