<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProductPublicationState;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BuyerCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('publication_state', ProductPublicationState::Published)
            ->with(['category:id,name', 'images', 'variants', 'producer.producerProfile'])
            ->latest('updated_at')
            ->get();

        return response()->json(['data' => $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category?->name,
            'producer_id' => $product->producer_id,
            'shop_name' => $product->producer->producerProfile?->farm_name,
            'image_url' => $product->images->first()
                ? Storage::disk($product->images->first()->disk)->url($product->images->first()->object_path)
                : null,
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'label' => $variant->option_label,
                'price_yen' => $variant->price_yen,
                'stock_quantity' => $variant->stock_quantity,
                'discount_bps' => $variant->discount_bps,
            ])->values(),
        ])->values()]);
    }
}
