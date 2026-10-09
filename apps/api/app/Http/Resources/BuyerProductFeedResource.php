<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerProductFeedResource extends JsonResource
{
    /** @return array{products:list<BuyerCatalogueProductResource>,total:?int,categories:?list<string>,next_cursor:?string} */
    public function toArray(Request $request): array
    {
        return [
            /** @var list<BuyerCatalogueProductResource> */
            'products' => $this->resource['products']->getCollection()
                ->map(fn (Product $product) => new BuyerCatalogueProductResource($product))->all(),
            /** @var int|null */
            'total' => $this->resource['total'],
            /** @var list<string>|null */
            'categories' => $this->resource['categories'],
            /** @var string|null */
            'next_cursor' => $this->resource['next_cursor'],
        ];
    }
}
