<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'name_phonetic' => ['required', 'string', 'regex:/^[ァ-ヺー\s]+$/u', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^0\d{2}-?\d{4}-?\d{4}$/', 'max:32'],
            'postal_code' => ['required', 'string', 'regex:/^\d{3}-?\d{4}$/', 'max:16'],
            'prefecture' => ['required', 'string', 'max:64'],
            'city' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'password' => [
                'required',
                'string',
                'confirmed',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value)
                        || preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,64}$/', $value) !== 1) {
                        $fail('パスワードは8文字以上で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。');
                    }
                },
            ],
            'terms_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'name_phonetic.required' => 'フリガナを入力してください。',
            'name_phonetic.regex' => 'フリガナは全角カタカナで入力してください。',
            'phone.required' => '電話番号を入力してください。',
            'phone.regex' => '電話番号を正しい形式で入力してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号を7桁の数字で入力してください。',
            'prefecture.required' => '都道府県を入力してください。',
            'city.required' => '市区町村を入力してください。',
            'address_line1.required' => '住所を入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワード確認が一致しません。',
            'terms_accepted.accepted' => '利用規約への同意が必要です。',
        ];
    }
}
