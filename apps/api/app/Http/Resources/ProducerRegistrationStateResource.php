<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{state:'none'|'pending'|'verified'|'expired'|'consumed',email:?string,server_time:string,otp_expires_at:?string,resend_available_at:?string,session_expires_at:?string,delivery_succeeded:?bool} $resource
 */
class ProducerRegistrationStateResource extends JsonResource
{
    /**
     * @return array{state:'none'|'pending'|'verified'|'expired'|'consumed',email:?string,server_time:string,otp_expires_at:?string,resend_available_at:?string,session_expires_at:?string,delivery_succeeded:?bool}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
