<?php

namespace App\Http\Requests;

use App\Models\ProducerOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProducerOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', ProducerOrder::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('keyword'))) {
            $this->merge(['keyword' => trim($this->input('keyword'))]);
        }
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'fulfillment_state' => ['sometimes', Rule::in(['all', 'received', 'processing', 'shipped'])],
            'status' => ['sometimes', Rule::in(['all', 'received', 'processing', 'shipped', 'cancelled', 'refunded'])],
            'period' => ['sometimes', Rule::in(['all', '30d', '90d', '12m', 'year', 'custom'])],
            'year' => ['exclude_unless:period,year', 'required_if:period,year', 'integer', 'between:2000,2100'],
            'from' => ['exclude_unless:period,custom', 'required_if:period,custom', 'date_format:Y-m-d'],
            'to' => ['exclude_unless:period,custom', 'required_if:period,custom', 'date_format:Y-m-d', 'after_or_equal:from'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
