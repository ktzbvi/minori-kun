<?php

namespace App\Policies;

use App\Enums\ProductPublicationState;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::Producer && $product->producer_id === $user->id)
            || ($user->role === UserRole::Buyer && $product->publication_state === ProductPublicationState::Published);
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Producer && $user->isSellingEligible();
    }

    public function update(User $user, Product $product): bool
    {
        return ($user->role === UserRole::Producer && $product->producer_id === $user->id) || $user->role === UserRole::Admin;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
