<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateProducerShopPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profile = $this->user()?->producerProfile;

        return $profile !== null && Gate::allows('update', $profile);
    }

    public function rules(): array
    {
        return ['photo' => ['required', 'file', 'max:5120'], 'expected_photo_id' => ['present', 'nullable', 'ulid']];
    }
}
