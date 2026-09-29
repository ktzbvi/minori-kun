<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class CompleteBuyerPasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Closure>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'token' => ['required', 'string', 'max:255'],
            'password' => [
                'required',
                'string',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value)
                        || preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,64}$/', $value) !== 1) {
                        $fail('パスワードは8〜64文字で、大文字・小文字・数字・記号をそれぞれ1文字以上含めてください。');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレスを入力してください。',
            'token.required' => '再設定用リンクが無効です。もう一度メールを送信してください。',
            'password.required' => '新しいパスワードを入力してください。',
            'password.confirmed' => '新しいパスワードと確認用パスワードが一致しません。',
        ];
    }
}
