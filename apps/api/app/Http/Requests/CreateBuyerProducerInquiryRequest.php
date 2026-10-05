<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBuyerProducerInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', Rule::in([
                '商品について',
                '配送・到着予定について',
                '商品の不備・不足について',
                'その他',
            ])],
            'message' => ['required', 'string', 'max:5000'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }
}
