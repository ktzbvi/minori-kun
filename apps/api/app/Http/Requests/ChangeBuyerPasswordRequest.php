<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class ChangeBuyerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string|Closure>> */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:4096'],
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
            'current_password.required' => '現在のパスワードを入力してください。',
            'password.required' => '新しいパスワードを入力してください。',
            'password.confirmed' => '新しいパスワードと確認用パスワードが一致しません。',
        ];
    }
}
