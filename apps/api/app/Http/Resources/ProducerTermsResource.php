<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{version:string,title:string,content:string,is_sample:bool} $resource
 */
class ProducerTermsResource extends JsonResource
{
    /** @return array{version:string,title:string,content:string,is_sample:bool} */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
