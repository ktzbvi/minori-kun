<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{state:'application_required'|'eligible',eligible_to_sell:bool,application_available:false} $resource
 */
class ProducerOnboardingStatusResource extends JsonResource
{
    /** @return array{state:'application_required'|'eligible',eligible_to_sell:bool,application_available:false} */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
