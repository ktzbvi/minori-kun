<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ProducerOrder;
use App\Models\User;

class ProducerOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Producer;
    }

    public function view(User $user, ProducerOrder $producerOrder): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::Producer && $producerOrder->producer_id === $user->id);
    }

    public function updateFulfillment(User $user, ProducerOrder $producerOrder): bool
    {
        return $user->role === UserRole::Producer && $producerOrder->producer_id === $user->id;
    }
}
