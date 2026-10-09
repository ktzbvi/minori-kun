<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Cursor;
use Throwable;

class IndexBuyerProductFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:40'],
            'cursor' => ['nullable', 'string', 'max:1024', function (string $attribute, mixed $value, Closure $fail): void {
                try {
                    $cursor = Cursor::fromEncoded($value);
                    if (! $cursor || ! is_string($cursor->parameter('id')) || ! is_string($cursor->parameter('created_at'))) {
                        $fail('The cursor is invalid.');
                    }
                } catch (Throwable) {
                    $fail('The cursor is invalid.');
                }
            }],
        ];
    }
}
