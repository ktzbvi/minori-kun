<?php

namespace App\Domain\Orders;

use App\Models\CartItem;
use App\Models\User;

class ReadBuyerCartCount
{
    /** @param list<array{variant_id:string,quantity:int,merge_target?:int}> $guestItems */
    public function execute(User $buyer, array $guestItems = []): int
    {
        $items = CartItem::query()
            ->join('carts', 'carts.id', '=', 'cart_items.cart_id')
            ->where('carts.buyer_id', $buyer->id)
            ->where('carts.state', 'active');
        $count = (int) (clone $items)->sum('cart_items.quantity');
        $targets = array_filter($guestItems, fn (array $item) => array_key_exists('merge_target', $item));
        $quantities = $targets === [] ? collect() : (clone $items)
            ->whereIn('cart_items.variant_id', array_column($targets, 'variant_id'))
            ->selectRaw('cart_items.variant_id, SUM(cart_items.quantity) as quantity')
            ->groupBy('cart_items.variant_id')
            ->pluck('quantity', 'variant_id');

        foreach ($guestItems as $item) {
            $count += array_key_exists('merge_target', $item)
                ? max(0, $item['merge_target'] - (int) $quantities->get($item['variant_id'], 0))
                : $item['quantity'];
        }

        return $count;
    }
}
