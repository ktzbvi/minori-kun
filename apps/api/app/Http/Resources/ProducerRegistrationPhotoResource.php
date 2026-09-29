<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{id:string,preview_url:string,expires_at:string} $resource
 */
class ProducerRegistrationPhotoResource extends JsonResource
{
    /** @return array{id:string,preview_url:string,expires_at:string} */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
