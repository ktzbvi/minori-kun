<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CompleteBuyerRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_phonetic' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'postal_code' => ['required', 'string', 'max:16'],
            'prefecture' => ['required', 'string', 'max:64'],
            'city' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->max(64)->mixedCase()->numbers()->symbols(),
            ],
            'terms_accepted' => ['accepted'],
        ];
    }
}
