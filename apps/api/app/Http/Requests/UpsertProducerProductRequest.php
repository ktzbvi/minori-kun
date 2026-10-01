<?php

namespace App\Http\Requests;

use App\Enums\ProductPublicationState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProducerProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('image_order'))) {
            $decoded = json_decode($this->string('image_order')->toString(), true);
            $this->merge(['image_order' => is_array($decoded) ? $decoded : null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'string',
                Rule::exists('categories', 'id')->where('is_enabled', true),
            ],
            'description' => ['required', 'string', 'max:10000'],
            'price_yen' => ['required', 'integer', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'delivery_fee_honshu_yen' => ['required', 'integer', 'min:0'],
            'delivery_fee_hokkaido_yen' => ['required', 'integer', 'min:0'],
            'delivery_fee_okinawa_yen' => ['required', 'integer', 'min:0'],
            'publication_state' => ['required', Rule::enum(ProductPublicationState::class)],
            'lock_version' => [$this->route('product') ? 'required' : 'nullable', 'integer', 'min:1'],
            'image_order' => ['required', 'array', 'min:1'],
            'image_order.*' => ['required', 'string', 'distinct', 'regex:/^(existing:[0-9A-HJKMNP-TV-Z]{26}|new:[0-9]+)$/i'],
            'new_images' => ['sometimes', 'array'],
            'new_images.*' => ['file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image_order.required' => '商品画像を1枚以上追加してください。',
            'image_order.min' => '商品画像を1枚以上追加してください。',
            'new_images.*.mimetypes' => 'JPEG、PNG、WebP形式の画像を選択してください。',
            'new_images.*.max' => '画像は10MB以下にしてください。',
        ];
    }
}
