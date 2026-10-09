<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuyerCartCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_items' => ['sometimes', 'array'],
            'guest_items.*.variant_id' => ['required', 'ulid', 'distinct'],
            'guest_items.*.quantity' => ['required', 'integer', 'min:1'],
            'guest_items.*.merge_target' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
