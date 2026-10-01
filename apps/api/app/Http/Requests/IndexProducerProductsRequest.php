<?php

namespace App\Http\Requests;

use App\Enums\ProductPublicationState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProducerProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['keyword', 'category', 'publication_state', 'stock_state'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'publication_state' => ['nullable', Rule::in(['all', ...array_map(
                static fn (ProductPublicationState $state): string => $state->value,
                ProductPublicationState::cases(),
            )])],
            'stock_state' => ['nullable', Rule::in(['all', 'in_stock', 'low_stock', 'out_of_stock'])],
        ];
    }
}
