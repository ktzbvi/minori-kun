<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{email:string,terms:array{version:string,title:string,content:string,is_sample:bool},photo:?array{id:string,preview_url:string,expires_at:string},session_expires_at:string} $resource
 */
class ProducerRegistrationDetailsResource extends JsonResource
{
    /**
     * @return array{email:string,terms:array{version:string,title:string,content:string,is_sample:bool},photo:?array{id:string,preview_url:string,expires_at:string},session_expires_at:string}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
