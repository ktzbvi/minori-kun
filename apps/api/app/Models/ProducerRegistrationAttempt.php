<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ProducerRegistrationAttempt extends DomainModel
{
    protected function casts(): array
    {
        return [
            'otp_generation' => 'integer',
            'verification_attempts' => 'integer',
            'resend_count' => 'integer',
            'delivery_succeeded' => 'boolean',
            'otp_delivered_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_consumed_at' => 'datetime',
            'verified_at' => 'datetime',
            'grant_expires_at' => 'datetime',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'invalidated_at' => 'datetime',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProducerRegistrationPhoto::class, 'attempt_id');
    }
}
