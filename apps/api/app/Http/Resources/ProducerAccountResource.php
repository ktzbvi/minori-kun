<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class ProducerAccountResource extends JsonResource
{
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->headers->set('Cache-Control', 'no-store, private');
    }

    /** @return array{farm_name:string,masked_email:string,shop_photo_url:?string,shop_photo_id:?string} */
    public function toArray(Request $request): array
    {
        $profile = $this->producerProfile;
        [$local, $domain] = explode('@', $this->email, 2);

        return [
            'farm_name' => (string) $profile->farm_name,
            'masked_email' => mb_substr($local, 0, 1).'••••••@'.$domain,
            'shop_photo_id' => $profile->shop_photo_id ? (string) $profile->shop_photo_id : null,
            'shop_photo_url' => $profile->shop_photo_id ? route('producer.shop-photo', ['id' => $profile->shop_photo_id]) : null,
        ];
    }
}
