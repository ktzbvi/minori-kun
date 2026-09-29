<?php

use App\Mail\BuyerPasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->withHeader('Origin', 'http://localhost:5173');
    $this->withSession(['_token' => 'test-csrf-token']);
    $this->withHeader('X-CSRF-TOKEN', 'test-csrf-token');
    config(['app.buyer_web_url' => 'http://localhost:5173']);
});

it('returns a neutral success response for registered and unknown buyer emails', function (): void {
    Mail::fake();
    $buyer = User::factory()->buyer()->create(['email' => 'buyer@example.test']);

    $this->postJson('/api/v1/buyer/password-reset/start', ['email' => $buyer->email])
        ->assertAccepted()
        ->assertJsonPath('data.message', 'パスワード再設定用メールを送信しました。');
    $this->postJson('/api/v1/buyer/password-reset/start', ['email' => 'unknown@example.test'])
        ->assertAccepted()
        ->assertJsonPath('data.message', 'パスワード再設定用メールを送信しました。');

    expect(PasswordResetToken::query()->where('user_id', $buyer->id)->count())->toBe(1);
    Mail::assertSent(BuyerPasswordResetMail::class, fn (BuyerPasswordResetMail $mail): bool => $mail->hasTo($buyer->email));
});

it('replaces an earlier reset token with a new 30 minute token', function (): void {
    Mail::fake();
    $buyer = User::factory()->buyer()->create();
    $oldToken = PasswordResetToken::query()->create([
        'user_id' => $buyer->id,
        'token_hash' => hash('sha256', 'old-token'),
        'expires_at' => now()->addMinutes(30),
    ]);

    $this->postJson('/api/v1/buyer/password-reset/start', ['email' => $buyer->email])->assertAccepted();

    expect(PasswordResetToken::query()->whereKey($oldToken->id)->exists())->toBeFalse();
    $token = PasswordResetToken::query()->where('user_id', $buyer->id)->sole();
    expect($token->expires_at)->toEqual(now()->addMinutes(30)->startOfSecond());
});

it('changes a buyer password once and rejects reuse of the token', function (): void {
    $buyer = User::factory()->buyer()->create(['password' => 'Old-password1!']);
    $token = 'buyer-reset-token';
    PasswordResetToken::query()->create([
        'user_id' => $buyer->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addMinutes(30),
    ]);

    $payload = [
        'email' => $buyer->email,
        'token' => $token,
        'password' => 'New-password1!',
        'password_confirmation' => 'New-password1!',
    ];

    $this->postJson('/api/v1/buyer/password-reset/complete', $payload)
        ->assertOk()
        ->assertJsonPath('data.redirect', '/login');

    expect(Hash::check('New-password1!', $buyer->refresh()->password))->toBeTrue();
    $this->postJson('/api/v1/buyer/password-reset/complete', $payload)
        ->assertUnprocessable()
        ->assertJsonPath('code', 'PASSWORD_RESET_TOKEN_INVALID');
});

it('rejects an expired reset token', function (): void {
    $buyer = User::factory()->buyer()->create();
    $token = 'expired-token';
    PasswordResetToken::query()->create([
        'user_id' => $buyer->id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->subMinute(),
    ]);

    $this->postJson('/api/v1/buyer/password-reset/complete', [
        'email' => $buyer->email,
        'token' => $token,
        'password' => 'New-password1!',
        'password_confirmation' => 'New-password1!',
    ])->assertUnprocessable()->assertJsonPath('code', 'PASSWORD_RESET_TOKEN_INVALID');
});
