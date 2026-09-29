<?php

namespace App\Models;

class BuyerRegistrationAttempt extends DomainModel
{
    protected function casts(): array
    {
        return [
            'otp_sent_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'resend_available_at' => 'datetime',
            'registration_session_expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
