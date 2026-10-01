<?php

namespace App\Http\Requests;

use App\Enums\ProductPublicationState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        ];
    }

    /**
     * @return array{data:array<string,mixed>,images:array<int,UploadedFile>,rejected_images:list<array{index:int,name:string,message:string}>}
     */
    public function productSubmission(): array
    {
        $data = $this->validated();
        $accepted = [];
        $rejected = [];
        $files = $this->file('new_images', []);

        foreach (is_array($files) ? $files : [] as $index => $file) {
            $validation = Validator::make(
                ['image' => $file],
                ['image' => ['bail', 'required', 'file', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:10240']],
                [
                    'image.image' => '有効な画像ファイルを選択してください。',
                    'image.mimetypes' => 'JPEG、PNG、WebP形式の画像を選択してください。',
                    'image.max' => '画像は10MB以下にしてください。',
                ],
            );

            if ($validation->fails()) {
                $rejected[] = [
                    'index' => (int) $index,
                    'name' => $file->getClientOriginalName(),
                    'message' => $validation->errors()->first('image'),
                ];
            } else {
                $accepted[(int) $index] = $file;
            }
        }

        $rejectedIndexes = array_column($rejected, 'index');
        $data['image_order'] = array_values(array_filter(
            $data['image_order'],
            fn (string $token): bool => ! str_starts_with($token, 'new:')
                || ! in_array((int) substr($token, 4), $rejectedIndexes, true),
        ));

        $referencedIndexes = array_map(
            fn (string $token): int => (int) substr($token, 4),
            array_values(array_filter($data['image_order'], fn (string $token): bool => str_starts_with($token, 'new:'))),
        );
        sort($referencedIndexes);
        $acceptedIndexes = array_keys($accepted);
        sort($acceptedIndexes);

        if ($referencedIndexes !== $acceptedIndexes) {
            throw ValidationException::withMessages([
                'image_order' => ['画像の指定が正しくありません。もう一度選択してください。'],
            ]);
        }
        if ($data['image_order'] === []) {
            throw ValidationException::withMessages([
                'image_order' => ['有効な商品画像を1枚以上追加してください。'],
            ]);
        }

        return ['data' => $data, 'images' => $accepted, 'rejected_images' => $rejected];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image_order.required' => '商品画像を1枚以上追加してください。',
            'image_order.min' => '商品画像を1枚以上追加してください。',
        ];
    }
}
