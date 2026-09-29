<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProducerRegistrationPhoto extends DomainModel
{
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ProducerRegistrationAttempt::class, 'attempt_id');
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_producer_id');
    }
}
