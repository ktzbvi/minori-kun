<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteProducerRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['prohibited'],
            'shop_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => [
                'required', 'string', 'min:8', 'max:64',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/',
            ],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'photo_id' => ['required', 'string', 'ulid'],
            'terms_version' => ['required', 'string', 'max:64'],
            'accepted_terms' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.prohibited' => '確認済みのメールアドレスは変更できません。',
            'shop_name.required' => 'ショップ名・農園名を入力してください。',
            'shop_name.max' => '255文字以内で入力してください。',
            'contact_name.required' => '担当者名を入力してください。',
            'contact_name.max' => '255文字以内で入力してください。',
            'phone.required' => '電話番号を入力してください。',
            'phone.max' => '32文字以内で入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => '8〜64文字で入力してください。',
            'password.max' => '8〜64文字で入力してください。',
            'password.regex' => '大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。',
            'password_confirmation.required' => '確認用パスワードを入力してください。',
            'password_confirmation.same' => 'パスワードが一致しません。',
            'photo_id.required' => 'ショッププロフィール写真を追加してください。',
            'photo_id.ulid' => '写真をアップロードし直してください。',
            'terms_version.required' => '生産者利用規約を確認して同意してください。',
            'accepted_terms.required' => '生産者利用規約への同意が必要です。',
            'accepted_terms.accepted' => '生産者利用規約への同意が必要です。',
        ];
    }
}
