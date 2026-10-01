<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\SaveProducerProduct;
use App\Enums\ProductPublicationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProducerProductsRequest;
use App\Http\Requests\UpsertProducerProductRequest;
use App\Http\Resources\ProducerProductListItemResource;
use App\Http\Resources\ProducerProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProducerProductController extends Controller
{
    public function options(): JsonResponse
    {
        return response()->json(['data' => [
            'categories' => Category::query()
                ->where('is_enabled', true)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]]);
    }

    public function index(IndexProducerProductsRequest $request): AnonymousResourceCollection
    {
        $producerId = (string) $request->user()->id;
        $validated = $request->validated();
        $keyword = $validated['keyword'] ?? null;
        $category = $validated['category'] ?? null;
        $publicationState = $validated['publication_state'] ?? 'all';
        $stockState = $validated['stock_state'] ?? 'all';

        $query = Product::query()
            ->where('producer_id', $producerId)
            ->with([
                'category:id,name',
                'images' => fn ($query) => $query->select('id', 'product_id', 'disk', 'object_path', 'display_order')
                    ->orderBy('display_order'),
                'variants' => fn ($query) => $query->select('id', 'product_id', 'price_yen', 'stock_quantity', 'discount_bps', 'display_order')
                    ->orderBy('display_order'),
            ])
            ->latest('updated_at');

        if (is_string($keyword) && $keyword !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $keyword).'%';
            $displayIdLike = null;
            if (preg_match('/^p-?([0-9a-z]+)$/i', $keyword, $matches) === 1) {
                $displayIdLike = '%'.str_replace(['%', '_'], ['\\%', '\\_'], strtolower($matches[1])).'%';
            }

            $query->where(function ($query) use ($like, $displayIdLike): void {
                $query->where('name', 'like', $like)->orWhere('id', 'like', $like);
                if ($displayIdLike !== null) {
                    $query->orWhere('id', 'like', $displayIdLike);
                }
            });
        }

        if (is_string($category) && $category !== '' && $category !== 'all') {
            $query->whereHas('category', fn ($query) => $query->where('name', $category));
        }

        if ($publicationState !== 'all') {
            $query->where('publication_state', ProductPublicationState::from($publicationState)->value);
        }

        if ($stockState !== 'all') {
            $query->whereHas('variants', function ($query) use ($stockState): void {
                $query->select('product_id')->groupBy('product_id');
                if ($stockState === 'out_of_stock') {
                    $query->havingRaw('SUM(stock_quantity) = 0');
                } elseif ($stockState === 'low_stock') {
                    $query->havingRaw('SUM(stock_quantity) > 0 AND SUM(stock_quantity) <= 20');
                } else {
                    $query->havingRaw('SUM(stock_quantity) > 0');
                }
            });
        }

        $products = $query->get();
        $categories = Product::query()
            ->where('producer_id', $producerId)
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->distinct()
            ->orderBy('categories.name')
            ->pluck('categories.name')
            ->values();

        return ProducerProductListItemResource::collection($products)
            ->additional(['meta' => [
                'total' => $products->count(),
                'categories' => $categories,
            ]]);
    }

    public function show(string $product): ProducerProductResource
    {
        $ownedProduct = Product::query()
            ->where('producer_id', request()->user()->id)
            ->with(['category:id,name', 'variants', 'images'])
            ->findOrFail($product);
        Gate::authorize('view', $ownedProduct);

        return new ProducerProductResource($ownedProduct);
    }

    public function store(UpsertProducerProductRequest $request, SaveProducerProduct $save): JsonResponse
    {
        Gate::authorize('create', Product::class);
        $product = $save->execute(
            $request->user(),
            $request->validated(),
            $request->file('new_images', []),
        );

        return (new ProducerProductResource($product))->response()->setStatusCode(201);
    }

    public function update(
        UpsertProducerProductRequest $request,
        string $product,
        SaveProducerProduct $save,
    ): ProducerProductResource {
        $ownedProduct = Product::query()
            ->where('producer_id', $request->user()->id)
            ->findOrFail($product);
        Gate::authorize('update', $ownedProduct);

        return new ProducerProductResource($save->execute(
            $request->user(),
            $request->validated(),
            $request->file('new_images', []),
            $ownedProduct,
        ));
    }
}
