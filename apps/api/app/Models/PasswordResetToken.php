<?php

namespace App\Models;

class PasswordResetToken extends DomainModel
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
