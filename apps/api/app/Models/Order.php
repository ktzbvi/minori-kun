<?php

namespace App\Models;

use App\Enums\PaymentState;
use App\Enums\RefundState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends DomainModel
{
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function producerOrders(): HasMany
    {
        return $this->hasMany(ProducerOrder::class);
    }

    public function deliveryAddress(): HasOne
    {
        return $this->hasOne(OrderDeliveryAddress::class);
    }

    protected function casts(): array
    {
        return [
            'payment_state' => PaymentState::class,
            'refund_state' => RefundState::class,
            'cancellation_deadline_at' => 'datetime',
            'placed_at' => 'datetime',
        ];
    }
}
