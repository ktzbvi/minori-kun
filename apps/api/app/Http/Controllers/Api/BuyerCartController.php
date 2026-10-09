<?php

namespace App\Http\Controllers\Api;

use App\Domain\Orders\ReadBuyerCartCount;
use App\Enums\ProductPublicationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\BuyerCartCountRequest;
use App\Http\Resources\BuyerCartCountResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BuyerCartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->payload($this->cartFor($request))]);
    }

    public function count(BuyerCartCountRequest $request, ReadBuyerCartCount $count): BuyerCartCountResource
    {
        return new BuyerCartCountResource($count->execute(
            $request->user(),
            $request->validated('guest_items', []),
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'ulid'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $this->cartFor($request);

        DB::transaction(function () use ($cart, $data): void {
            $variant = $this->purchasableVariant($data['variant_id']);
            $item = CartItem::query()->lockForUpdate()->firstOrNew([
                'cart_id' => $cart->id,
                'variant_id' => $variant->id,
            ]);
            $quantity = ($item->exists ? $item->quantity : 0) + $data['quantity'];

            $this->assertAvailable($variant, $quantity);
            $item->quantity = $quantity;
            $item->save();
        });

        return response()->json(['data' => $this->payload($cart->fresh())], 201);
    }

    public function update(Request $request, string $item): JsonResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $cart = $this->cartFor($request);

        DB::transaction(function () use ($cart, $item, $data): void {
            $cartItem = CartItem::query()->where('cart_id', $cart->id)->lockForUpdate()->findOrFail($item);
            $variant = $this->purchasableVariant($cartItem->variant_id);

            $this->assertAvailable($variant, $data['quantity']);
            $cartItem->update(['quantity' => $data['quantity']]);
        });

        return response()->json(['data' => $this->payload($cart->fresh())]);
    }

    public function destroy(Request $request, string $item): JsonResponse
    {
        $cart = $this->cartFor($request);
        CartItem::query()->where('cart_id', $cart->id)->findOrFail($item)->delete();

        return response()->json(['data' => $this->payload($cart->fresh())]);
    }

    private function cartFor(Request $request): Cart
    {
        return Cart::query()->firstOrCreate([
            'buyer_id' => $request->user()->id,
            'state' => 'active',
        ]);
    }

    private function purchasableVariant(string $id): ProductVariant
    {
        $variant = ProductVariant::query()
            ->with('product')
            ->lockForUpdate()
            ->findOrFail($id);

        if ($variant->product->publication_state !== ProductPublicationState::Published) {
            throw ValidationException::withMessages(['variant_id' => ['This product is unavailable.']]);
        }

        return $variant;
    }

    private function assertAvailable(ProductVariant $variant, int $quantity): void
    {
        if ($quantity > $variant->stock_quantity) {
            throw ValidationException::withMessages(['quantity' => ['Requested quantity is unavailable.']]);
        }
    }

    /** @return array<string, mixed> */
    private function payload(Cart $cart): array
    {
        $cart->load([
            'items.variant.product.producer.producerProfile',
            'items.variant.product.images',
        ]);

        return [
            'id' => $cart->id,
            'items' => $cart->items->map(function (CartItem $item): array {
                $product = $item->variant->product;
                $image = $product->images->first();

                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'variant_id' => $item->variant->id,
                    'variant_label' => $item->variant->option_label,
                    'stock_quantity' => $item->variant->stock_quantity,
                    'discount_bps' => $item->variant->discount_bps,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'producer_id' => $product->producer_id,
                    'shop_name' => $product->producer->producerProfile?->farm_name,
                    'delivery_fee_honshu_yen' => $product->delivery_fee_honshu_yen,
                    'delivery_fee_hokkaido_yen' => $product->delivery_fee_hokkaido_yen,
                    'delivery_fee_okinawa_yen' => $product->delivery_fee_okinawa_yen,
                    'image_url' => $image
                        ? Storage::disk($image->disk)->url($image->object_path)
                        : null,
                    'unit_price_yen' => $item->variant->price_yen,
                ];
            })->values(),
        ];
    }
}
