<?php

namespace App\Models;

use App\Enums\SettlementState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProducerSettlement extends DomainModel
{
    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SettlementLine::class, 'settlement_id');
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class, 'settlement_id');
    }

    protected function casts(): array
    {
        return ['state' => SettlementState::class, 'period_start' => 'date', 'period_end' => 'date', 'finalized_at' => 'datetime'];
    }
}
