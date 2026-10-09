<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerCartCountResource extends JsonResource
{
    /** @return array{count:int} */
    public function toArray(Request $request): array
    {
        return ['count' => (int) $this->resource];
    }
}
