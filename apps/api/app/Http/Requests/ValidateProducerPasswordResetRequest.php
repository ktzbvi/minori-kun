<?php

namespace App\Http\Requests;

class ValidateProducerPasswordResetRequest extends ProducerPasswordResetRequest
{
    public function rules(): array
    {
        return [...parent::rules(), 'token' => ['required', 'string', 'size:64', 'regex:/^[A-Za-z0-9]+$/']];
    }
}
