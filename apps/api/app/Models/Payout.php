<?php

namespace App\Models;

use App\Enums\PayoutState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends DomainModel
{
    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ProducerSettlement::class, 'settlement_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PayoutDocument::class);
    }

    protected function casts(): array
    {
        return ['state' => PayoutState::class, 'due_on' => 'date', 'paid_on' => 'date'];
    }
}
