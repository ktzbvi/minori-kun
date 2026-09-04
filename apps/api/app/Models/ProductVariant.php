<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class ProductVariant extends DomainModel
{
    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant): void {
            if ($variant->price_yen < 0 || $variant->stock_quantity < 0 || $variant->discount_bps < 0 || $variant->discount_bps > 10000) {
                throw ValidationException::withMessages([
                    'variant' => ['価格・在庫・割引率の値が不正です。'],
                ]);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'price_yen' => 'integer', 'stock_quantity' => 'integer', 'discount_bps' => 'integer'];
    }
}
