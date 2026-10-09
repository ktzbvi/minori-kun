<?php

namespace App\Domain\Catalog;

use App\Enums\ProductPublicationState;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Pagination\CursorPaginator;

class BrowseBuyerProductFeed
{
    /** @return array{products:CursorPaginator,total:?int,categories:?list<string>,next_cursor:?string} */
    public function execute(?string $category, int $perPage, ?string $cursor): array
    {
        $query = Product::query()->where('publication_state', ProductPublicationState::Published);
        if ($category !== null && $category !== 'all') {
            $categoryId = Category::query()->where('name', $category)->value('id');
            if ($categoryId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('category_id', $categoryId);
            }
        }

        // Metadata belongs to the first batch, not every cursor request.
        $total = $cursor === null ? (clone $query)->count() : null;
        $products = $query
            ->with(['category:id,name', 'images', 'variants', 'producer.producerProfile'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
        $categories = $cursor === null ? Category::query()
            ->whereHas('products', fn ($query) => $query->where('publication_state', ProductPublicationState::Published))
            ->orderBy('display_order')
            ->orderBy('name')
            ->pluck('name')
            ->all() : null;

        return [
            'products' => $products,
            'total' => $total,
            'categories' => $categories,
            'next_cursor' => $products->nextCursor()?->encode(),
        ];
    }
}
