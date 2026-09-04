<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class CurrentSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('buyerProfile', 'producerProfile', 'payjpTenant.screenings');
        $screenings = $this->payjpTenant?->screenings->pluck('state', 'card_brand');

        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at !== null,
            'display_name' => match ($this->role) {
                UserRole::Buyer => $this->buyerProfile?->name,
                UserRole::Producer => $this->producerProfile?->farm_name,
                UserRole::Admin => '管理者',
            },
            'producer' => $this->when($this->role === UserRole::Producer, [
                'eligible_to_sell' => $this->isSellingEligible(),
                'visa_status' => $screenings?->get('visa')?->value,
                'mastercard_status' => $screenings?->get('mastercard')?->value,
            ]),
        ];
    }
}
