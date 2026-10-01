<?php

use App\Mail\ProducerRegistrationOtpMail;
use App\Models\ProducerRegistrationAttempt;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->withHeader('Origin', 'http://localhost:5174');
    $this->withSession(['_token' => 'test-csrf-token']);
    $this->withHeader('X-CSRF-TOKEN', 'test-csrf-token');
    Carbon::setTestNow('2026-09-30 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('rejects an already registered Producer email before sending an OTP', function (): void {
    Mail::fake();
    User::factory()->producer()->create(['email' => 'registered@example.test']);

    $this->postJson('/api/v1/producer/registration/code', [
        'email' => ' Registered@Example.Test ',
    ])
        ->assertStatus(409)
        ->assertJsonPath('code', 'ACCOUNT_EXISTS')
        ->assertJsonPath('message', 'このメールアドレスはすでに登録されています。')
        ->assertJsonPath('errors.email.0', 'このメールアドレスはすでに登録されています。');

    expect(ProducerRegistrationAttempt::query()->exists())->toBeFalse();
    Mail::assertNotSent(ProducerRegistrationOtpMail::class);
});
