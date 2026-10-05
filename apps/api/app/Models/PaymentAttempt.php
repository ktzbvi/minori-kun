<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends DomainModel
{
    public function producerOrder(): BelongsTo
    {
        return $this->belongsTo(ProducerOrder::class);
    }
}
