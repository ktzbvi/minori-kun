<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('authenticates each account only through its matching portal', function (UserRole $role): void {
    $this->withHeader('Origin', 'http://localhost:5173');
    $user = User::factory()->create([
        'role' => $role,
        'email' => "{$role->value}@example.test",
        'password' => Hash::make('ValidPassword!123'),
    ]);

    $this->postJson("/api/v1/{$role->value}/auth/login", [
        'email' => $user->email,
        'password' => 'ValidPassword!123',
    ])->assertOk()->assertJsonPath('data.role', $role->value);

    $this->getJson("/api/v1/{$role->value}/me")
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
})->with(UserRole::cases());

it('returns the same neutral failure for a wrong role and unknown account', function (): void {
    $user = User::factory()->buyer()->create([
        'email' => 'buyer@example.test',
        'password' => Hash::make('ValidPassword!123'),
    ]);

    $wrongRole = $this->postJson('/api/v1/producer/auth/login', [
        'email' => $user->email,
        'password' => 'ValidPassword!123',
    ])->assertUnprocessable();

    $unknown = $this->postJson('/api/v1/producer/auth/login', [
        'email' => 'missing@example.test',
        'password' => 'ValidPassword!123',
    ])->assertUnprocessable();

    expect($wrongRole->json('message'))->toBe($unknown->json('message'))
        ->and($wrongRole->json('code'))->toBe('AUTHENTICATION_FAILED');
});

it('logs out and invalidates the current session', function (): void {
    $this->withHeader('Origin', 'http://localhost:5173');
    $user = User::factory()->buyer()->create(['password' => Hash::make('ValidPassword!123')]);

    $this->postJson('/api/v1/buyer/auth/login', ['email' => $user->email, 'password' => 'ValidPassword!123'])->assertOk();
    $this->postJson('/api/v1/buyer/auth/logout')->assertOk();
    $this->getJson('/api/v1/buyer/me')->assertUnauthorized();
});

it('rate limits repeated portal login failures', function (): void {
    foreach (range(1, 5) as $_) {
        $this->postJson('/api/v1/buyer/auth/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/buyer/auth/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertTooManyRequests();
});
