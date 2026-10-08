<?php

namespace App\Http\Requests;

use App\Models\ProducerOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProducerFulfillmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', ProducerOrder::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'fulfillment_state' => ['required', Rule::in(['received', 'processing', 'shipped'])],
            'expected_state' => ['required', Rule::in(['received', 'processing', 'shipped'])],
        ];
    }
}
