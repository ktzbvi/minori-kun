<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Product */
class BuyerCatalogueProductResource extends JsonResource
{
    /**
     * @return array{
     *   id:string,
     *   name:string,
     *   description:string,
     *   category:?string,
     *   producer_id:string,
     *   shop_name:?string,
     *   image_url:?string,
     *   variants:list<array{id:string,label:string,price_yen:int,stock_quantity:int,discount_bps:int}>
     * }
     */
    public function toArray(Request $request): array
    {
        $image = $this->images->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category?->name,
            'producer_id' => $this->producer_id,
            /** @var string|null */
            'shop_name' => $this->producer->producerProfile?->farm_name,
            /** @var string|null */
            'image_url' => $image ? Storage::disk($image->disk)->url($image->object_path) : null,
            'variants' => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'label' => $variant->option_label,
                'price_yen' => $variant->price_yen,
                'stock_quantity' => $variant->stock_quantity,
                'discount_bps' => $variant->discount_bps,
            ])->values()->all(),
        ];
    }
}
