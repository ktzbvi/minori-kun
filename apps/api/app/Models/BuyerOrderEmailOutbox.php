<?php

namespace App\Models;

class BuyerOrderEmailOutbox extends DomainModel
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'locked_until' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
