<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProducerProductResource extends JsonResource
{
    /**
     * @return array{
     *   id:string,
     *   name:string,
     *   description:string,
     *   category_id:string,
     *   category_name:?string,
     *   price_yen:int,
     *   stock_quantity:int,
     *   discount_bps:int,
     *   delivery_fee_honshu_yen:int,
     *   delivery_fee_hokkaido_yen:int,
     *   delivery_fee_okinawa_yen:int,
     *   publication_state:'draft'|'published'|'unpublished',
     *   lock_version:int,
     *   images:list<array{id:string,url:string,display_order:int}>
     * }
     */
    public function toArray(Request $request): array
    {
        $variant = $this->variants->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'price_yen' => (int) ($variant?->price_yen ?? 0),
            'stock_quantity' => (int) ($variant?->stock_quantity ?? 0),
            'discount_bps' => (int) ($variant?->discount_bps ?? 0),
            'delivery_fee_honshu_yen' => (int) $this->delivery_fee_honshu_yen,
            'delivery_fee_hokkaido_yen' => (int) $this->delivery_fee_hokkaido_yen,
            'delivery_fee_okinawa_yen' => (int) $this->delivery_fee_okinawa_yen,
            'publication_state' => (string) $this->publication_state->value,
            'lock_version' => (int) $this->lock_version,
            'images' => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => Storage::disk($image->disk)->url($image->object_path),
                'display_order' => $image->display_order,
            ])->values()->all(),
        ];
    }
}
