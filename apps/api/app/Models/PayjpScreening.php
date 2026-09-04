<?php

namespace App\Models;

use App\Enums\ScreeningState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayjpScreening extends DomainModel
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PayjpTenant::class, 'tenant_id');
    }

    protected function casts(): array
    {
        return ['state' => ScreeningState::class, 'available_on' => 'date', 'provider_updated_at' => 'datetime'];
    }
}
