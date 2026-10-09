<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\BrowseBuyerProductFeed;
use App\Enums\ProductPublicationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexBuyerCatalogRequest;
use App\Http\Requests\IndexBuyerProductFeedRequest;
use App\Http\Resources\BuyerCatalogueProductResource;
use App\Http\Resources\BuyerProductFeedResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuyerCatalogController extends Controller
{
    public function index(IndexBuyerCatalogRequest $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->where('publication_state', ProductPublicationState::Published)
            ->with(['category:id,name', 'images', 'variants', 'producer.producerProfile'])
            ->latest('updated_at');
        $validated = $request->validated();
        if (array_key_exists('variant_ids', $validated)) {
            $query->whereHas('variants', fn ($query) => $query->whereIn('id', $validated['variant_ids']));
        }

        return BuyerCatalogueProductResource::collection($query->get());
    }

    public function feed(IndexBuyerProductFeedRequest $request, BrowseBuyerProductFeed $browse): BuyerProductFeedResource
    {
        $validated = $request->validated();

        return new BuyerProductFeedResource($browse->execute(
            $validated['category'] ?? null,
            (int) ($validated['per_page'] ?? 4),
            $validated['cursor'] ?? null,
        ));
    }
}
