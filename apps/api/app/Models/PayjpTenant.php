<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayjpTenant extends DomainModel
{
    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(PayjpScreening::class, 'tenant_id');
    }

    protected function casts(): array
    {
        return ['last_authoritative_sync_at' => 'datetime'];
    }
}
