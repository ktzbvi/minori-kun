<?php

namespace App\Models;

use App\Enums\ProducerOperationalState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProducerProfile extends DomainModel
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'operational_state' => ProducerOperationalState::class,
            'selling_eligible_at' => 'datetime',
            'selling_suspended_at' => 'datetime',
        ];
    }
}
