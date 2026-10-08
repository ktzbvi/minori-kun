<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('product_code', 12)->nullable()->unique();
        });
        // DATA-004 / P06-01: include deleted products so codes cannot be reused.
        DB::table('products')->orderBy('id')->chunkById(200, function ($products): void {
            foreach ($products as $product) {
                do {
                    $code = 'P-'.strtoupper(bin2hex(random_bytes(5)));
                } while (DB::table('products')->where('product_code', $code)->exists());
                DB::table('products')->where('id', $product->id)->update(['product_code' => $code]);
            }
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->string('product_code', 12)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['product_code']);
            $table->dropColumn('product_code');
        });
    }
};
