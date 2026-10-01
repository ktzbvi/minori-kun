<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBuyerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_phonetic' => ['required', 'string', 'regex:/^[ァ-ヺー\s]+$/u', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^0\d{2}-?\d{4}-?\d{4}$/', 'max:32'],
            'postal_code' => ['required', 'string', 'regex:/^\d{3}-?\d{4}$/', 'max:16'],
            'prefecture' => ['required', 'string', 'max:64'],
            'city' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'name_phonetic.required' => 'フリガナを入力してください。',
            'name_phonetic.regex' => 'フリガナは全角カタカナで入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレスを入力してください。',
            'phone.required' => '電話番号を入力してください。',
            'phone.regex' => '電話番号を正しい形式で入力してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号を7桁の数字で入力してください。',
            'prefecture.required' => '都道府県を入力してください。',
            'city.required' => '市区町村を入力してください。',
            'address_line1.required' => '住所を入力してください。',
        ];
    }
}
