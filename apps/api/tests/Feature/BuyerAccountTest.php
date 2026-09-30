<?php

use App\Mail\BuyerEmailChangeVerificationMail;
use App\Models\BuyerAddress;
use App\Models\BuyerProfile;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

function buyerAccountPayload(User $buyer, array $overrides = []): array
{
    return array_merge([
        'name' => '山田 太郎',
        'name_phonetic' => 'ヤマダ タロウ',
        'email' => $buyer->email,
        'phone' => '090-1234-5678',
        'postal_code' => '123-4567',
        'prefecture' => '東京都',
        'city' => '新宿区',
        'address_line1' => '西新宿1-2-3',
        'address_line2' => 'テストビル101',
    ], $overrides);
}

it('returns only the authenticated buyers profile and default address', function (): void {
    $buyer = User::factory()->buyer()->create();
    BuyerProfile::query()->create(['user_id' => $buyer->id, 'name' => '山田 太郎', 'name_phonetic' => 'ヤマダ タロウ', 'phone' => '09012345678']);
    BuyerAddress::query()->create([
        'buyer_id' => $buyer->id,
        'recipient_name' => '山田 太郎',
        'phone' => '09012345678',
        'postal_code' => '1234567',
        'prefecture' => '東京都',
        'city' => '新宿区',
        'address_line1' => '西新宿1-2-3',
        'is_default' => true,
    ]);

    $this->actingAs($buyer)
        ->getJson('/api/v1/buyer/account/profile')
        ->assertOk()
        ->assertJsonPath('data.name', '山田 太郎')
        ->assertJsonPath('data.city', '新宿区');
});

it('updates profile data and keeps a changed email pending until link verification', function (): void {
    Mail::fake();
    $buyer = User::factory()->buyer()->create(['email' => 'before@example.test']);

    $this->actingAs($buyer)
        ->patchJson('/api/v1/buyer/account/profile', buyerAccountPayload($buyer, ['email' => 'after@example.test']))
        ->assertOk()
        ->assertJsonPath('data.email_change_pending', true);

    expect($buyer->refresh()->email)->toBe('before@example.test')
        ->and($buyer->pending_email)->toBe('after@example.test');
    expect(EmailVerificationToken::query()->where('user_id', $buyer->id)->where('purpose', 'buyer_email_change')->exists())->toBeTrue();
    Mail::assertSent(BuyerEmailChangeVerificationMail::class, fn (BuyerEmailChangeVerificationMail $mail): bool => $mail->hasTo('after@example.test'));
});

it('activates a pending email only after its one-time verification link is opened', function (): void {
    $buyer = User::factory()->buyer()->create([
        'email' => 'before@example.test',
        'pending_email' => 'after@example.test',
    ]);
    $rawToken = 'buyeremailchangetoken';
    EmailVerificationToken::query()->create([
        'user_id' => $buyer->id,
        'purpose' => 'buyer_email_change',
        'token_hash' => hash('sha256', $rawToken),
        'expires_at' => now()->addMinutes(30),
    ]);

    $this->get("/api/v1/buyer/account/email-verifications/{$rawToken}")
        ->assertRedirect('http://localhost:5173/my-page/member-info?emailChange=success');

    expect($buyer->refresh()->email)->toBe('after@example.test')
        ->and($buyer->pending_email)->toBeNull();
});

it('changes a buyers password only after the current password is supplied', function (): void {
    $buyer = User::factory()->buyer()->create(['password' => 'Current-password1!']);

    $this->actingAs($buyer)
        ->putJson('/api/v1/buyer/account/password', [
            'current_password' => 'Current-password1!',
            'password' => 'New-password1!',
            'password_confirmation' => 'New-password1!',
        ])
        ->assertOk()
        ->assertJsonPath('data.changed', true);

    expect(Hash::check('New-password1!', $buyer->refresh()->password))->toBeTrue();
});
