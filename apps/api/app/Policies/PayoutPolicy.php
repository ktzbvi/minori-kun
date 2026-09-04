<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payout;
use App\Models\User;

class PayoutPolicy
{
    public function view(User $user, Payout $payout): bool
    {
        return $user->role === UserRole::Admin
            || ($user->role === UserRole::Producer && $payout->settlement->producer_id === $user->id);
    }
}
