<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProducerProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $variant = $this->variants->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'price_yen' => $variant?->price_yen ?? 0,
            'stock_quantity' => $variant?->stock_quantity ?? 0,
            'discount_bps' => $variant?->discount_bps ?? 0,
            'delivery_fee_honshu_yen' => $this->delivery_fee_honshu_yen,
            'delivery_fee_hokkaido_yen' => $this->delivery_fee_hokkaido_yen,
            'delivery_fee_okinawa_yen' => $this->delivery_fee_okinawa_yen,
            'publication_state' => $this->publication_state->value,
            'lock_version' => $this->lock_version,
            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => Storage::disk($image->disk)->url($image->object_path),
                'display_order' => $image->display_order,
            ])->values(),
        ];
    }
}
