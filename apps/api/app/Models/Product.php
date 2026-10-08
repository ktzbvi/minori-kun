<?php

namespace App\Models;

use App\Domain\Identity\NextPublicReference;
use App\Enums\ProductPublicationState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends DomainModel
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            $product->product_code = app(NextPublicReference::class)->next('product');
        });
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('display_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('display_order');
    }

    protected function casts(): array
    {
        return [
            'publication_state' => ProductPublicationState::class,
            'delivery_fee_honshu_yen' => 'integer',
            'delivery_fee_hokkaido_yen' => 'integer',
            'delivery_fee_okinawa_yen' => 'integer',
            'lock_version' => 'integer',
        ];
    }
}
