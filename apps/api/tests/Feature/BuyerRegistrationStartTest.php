<?php

use App\Mail\BuyerRegistrationOtpMail;
use App\Models\BuyerRegistrationAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->withHeader('Origin', 'http://localhost:5173');
    $this->withSession(['_token' => 'test-csrf-token']);
    $this->withHeader('X-CSRF-TOKEN', 'test-csrf-token');
    Carbon::setTestNow('2026-09-28 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('starts a Buyer registration attempt and sends a six-digit OTP without creating an account', function (): void {
    Mail::fake();

    $response = $this->postJson('/api/v1/buyer/registration/start', [
        'email' => ' New.Buyer@Example.Test ',
    ]);

    $response->assertAccepted()
        ->assertJsonPath('data.email', 'new.buyer@example.test')
        ->assertJsonPath('data.next', 'verify-otp');

    $attempt = BuyerRegistrationAttempt::query()->where('email', 'new.buyer@example.test')->sole();

    expect($attempt->otp_expires_at)->toEqual(now()->addMinutes(3))
        ->and($attempt->resend_available_at)->toEqual(now()->addMinutes(3))
        ->and($attempt->registration_session_expires_at)->toEqual(now()->addDay())
        ->and(User::query()->where('email', 'new.buyer@example.test')->exists())->toBeFalse();

    expect(session('buyer_registration_attempt_id'))->toBe($attempt->id);

    Mail::assertSent(BuyerRegistrationOtpMail::class, function (BuyerRegistrationOtpMail $mail): bool {
        return $mail->hasTo('new.buyer@example.test') && preg_match('/^\\d{6}$/', $mail->otp) === 1
            && Hash::check($mail->otp, BuyerRegistrationAttempt::query()->sole()->otp_hash);
    });
});

it('does not send another registration OTP until the three-minute resend time has passed', function (): void {
    Mail::fake();

    $this->postJson('/api/v1/buyer/registration/start', ['email' => 'buyer@example.test'])
        ->assertAccepted();

    $this->postJson('/api/v1/buyer/registration/start', ['email' => 'buyer@example.test'])
        ->assertStatus(429)
        ->assertJsonPath('code', 'OTP_RESEND_NOT_AVAILABLE');

    Mail::assertSent(BuyerRegistrationOtpMail::class, 1);
});

it('validates the registration email before creating an attempt', function (): void {
    Mail::fake();

    $this->postJson('/api/v1/buyer/registration/start', ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(BuyerRegistrationAttempt::query()->exists())->toBeFalse();
    Mail::assertNothingSent();
});

it('verifies a current OTP and preserves the registration session', function (): void {
    $attempt = BuyerRegistrationAttempt::query()->create([
        'email' => 'buyer@example.test',
        'otp_hash' => Hash::make('123456'),
        'otp_sent_at' => now(),
        'otp_expires_at' => now()->addMinutes(3),
        'resend_available_at' => now()->addMinutes(3),
        'registration_session_expires_at' => now()->addDay(),
    ]);

    $this->withSession([
        '_token' => 'test-csrf-token',
        'buyer_registration_attempt_id' => $attempt->id,
    ])->withHeader('X-CSRF-TOKEN', 'test-csrf-token')
        ->postJson('/api/v1/buyer/registration/verify-otp', ['code' => '123456'])
        ->assertOk()
        ->assertJsonPath('data.next', 'registration-details');

    expect($attempt->refresh()->verified_at)->not->toBeNull()
        ->and(session('buyer_registration_attempt_id'))->toBe($attempt->id);
});

it('rejects an incorrect or expired OTP without marking the email verified', function (): void {
    $attempt = BuyerRegistrationAttempt::query()->create([
        'email' => 'buyer@example.test',
        'otp_hash' => Hash::make('123456'),
        'otp_sent_at' => now()->subMinutes(4),
        'otp_expires_at' => now()->subMinute(),
        'resend_available_at' => now(),
        'registration_session_expires_at' => now()->addDay(),
    ]);

    $this->withSession([
        '_token' => 'test-csrf-token',
        'buyer_registration_attempt_id' => $attempt->id,
    ])->withHeader('X-CSRF-TOKEN', 'test-csrf-token')
        ->postJson('/api/v1/buyer/registration/verify-otp', ['code' => '123456'])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'OTP_INVALID');

    expect($attempt->refresh()->verified_at)->toBeNull();
});

it('resends an OTP after the resend time and invalidates the previous code', function (): void {
    Mail::fake();
    $attempt = BuyerRegistrationAttempt::query()->create([
        'email' => 'buyer@example.test',
        'otp_hash' => Hash::make('123456'),
        'otp_sent_at' => now()->subMinutes(3),
        'otp_expires_at' => now()->addMinute(),
        'resend_available_at' => now(),
        'registration_session_expires_at' => now()->addDay(),
    ]);

    $this->withSession([
        '_token' => 'test-csrf-token',
        'buyer_registration_attempt_id' => $attempt->id,
    ])->withHeader('X-CSRF-TOKEN', 'test-csrf-token')
        ->postJson('/api/v1/buyer/registration/resend-otp')
        ->assertAccepted();

    expect(Hash::check('123456', $attempt->refresh()->otp_hash))->toBeFalse();
    Mail::assertSent(BuyerRegistrationOtpMail::class, 1);
});
