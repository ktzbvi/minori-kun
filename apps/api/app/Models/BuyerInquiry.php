<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerInquiry extends DomainModel
{
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }
}
