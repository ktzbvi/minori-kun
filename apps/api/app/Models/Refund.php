<?php

namespace App\Models;

use App\Enums\RefundState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends DomainModel
{
    public function paymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    protected function casts(): array
    {
        return [
            'state' => RefundState::class,
            'authoritative_updated_at' => 'datetime',
        ];
    }
}
