<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::Buyer && $order->buyer_id === $user->id);
    }
}
