<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexBuyerCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_ids' => ['sometimes', 'array'],
            'variant_ids.*' => ['filled', 'string', 'ulid', 'distinct'],
        ];
    }
}
