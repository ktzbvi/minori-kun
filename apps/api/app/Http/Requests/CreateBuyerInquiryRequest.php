<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBuyerInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => '件名を入力してください。',
            'subject.max' => '件名は255文字以内で入力してください。',
            'message.required' => 'お問い合わせ内容を入力してください。',
            'message.max' => 'お問い合わせ内容は5000文字以内で入力してください。',
            'idempotency_key.required' => '送信情報が不足しています。もう一度お試しください。',
            'idempotency_key.uuid' => '送信情報が無効です。画面を開き直してもう一度お試しください。',
        ];
    }
}
