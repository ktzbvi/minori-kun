<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ProducerProfile;
use App\Models\User;

class ProducerProfilePolicy
{
    public function view(User $user, ProducerProfile $profile): bool
    {
        return $user->role === UserRole::Admin || $profile->user_id === $user->id;
    }

    public function update(User $user, ProducerProfile $profile): bool
    {
        return $profile->user_id === $user->id;
    }
}
