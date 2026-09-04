<?php

namespace App\Models;

use App\Enums\FulfillmentState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProducerOrder extends DomainModel
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return ['fulfillment_state' => FulfillmentState::class];
    }
}
