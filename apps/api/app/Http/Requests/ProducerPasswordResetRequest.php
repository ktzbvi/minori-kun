<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProducerPasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email:rfc', 'max:255']];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレスを入力してください。',
            'password.required' => '新しいパスワードを入力してください。',
            'password.min' => '8〜64文字で入力してください。',
            'password.max' => '8〜64文字で入力してください。',
            'password.regex' => '大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。',
            'password_confirmation.required' => '確認用パスワードを入力してください。',
            'password_confirmation.same' => 'パスワードが一致しません。',
            'token.*' => '再設定用リンクが無効です。もう一度メールを送信してください。',
        ];
    }
}
