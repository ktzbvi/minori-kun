<?php

namespace App\Http\Requests;

class CompleteProducerPasswordResetRequest extends ValidateProducerPasswordResetRequest
{
    public function rules(): array
    {
        return [...parent::rules(),
            'password' => ['required', 'string', 'min:8', 'max:64', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[^\p{L}\p{N}\s]/u'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ];
    }
}
