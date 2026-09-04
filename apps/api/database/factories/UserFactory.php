<?php

namespace Database\Factories;

use App\Enums\AccountState;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'role' => UserRole::Buyer,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'account_state' => AccountState::Active,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ];
    }

    public function buyer(): static
    {
        return $this->state(['role' => UserRole::Buyer]);
    }

    public function producer(): static
    {
        return $this->state(['role' => UserRole::Producer]);
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::Admin]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
