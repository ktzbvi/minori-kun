<?php

use App\Enums\AccountState;
use App\Http\Requests\CompleteProducerPasswordResetRequest;
use App\Http\Requests\CompleteProducerRegistrationRequest;
use App\Mail\ProducerPasswordResetMail;
use App\Models\AuditEvent;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

// FR-P-015, P15-01/02/03, SEC-009, AT-P-015.
beforeEach(function (): void {
    $this->withHeader('Origin', 'http://localhost:5174');
    $this->withSession(['_token' => 'test-csrf-token']);
    $this->withHeader('X-CSRF-TOKEN', 'test-csrf-token');
    config(['producer-password-reset.web_url' => 'http://localhost:5174']);
});

function producerResetPayload(User $producer, string $token = ''): array
{
    $token = $token ?: str_repeat('a', 64);
    PasswordResetToken::query()->create([
        'user_id' => $producer->id, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes(30),
    ]);

    return ['email' => $producer->email, 'token' => $token, 'password' => ' New-password1! ', 'password_confirmation' => ' New-password1! '];
}

it('returns identical neutral responses without exposing role or account state', function (): void {
    Mail::fake();
    $producer = User::factory()->producer()->create();
    $buyer = User::factory()->buyer()->create();
    $admin = User::factory()->admin()->create();
    $suspended = User::factory()->producer()->create(['account_state' => AccountState::Suspended]);
    $responses = [];
    foreach ([$producer->email, $buyer->email, $admin->email, $suspended->email, 'unknown@example.test'] as $email) {
        $responses[] = $this->postJson('/api/v1/producer/password-reset/start', ['email' => $email])->assertAccepted()->json();
    }
    foreach ($responses as $response) {
        expect($response)->toBe($responses[0]);
    }
    expect(PasswordResetToken::count())->toBe(1);
    Mail::assertSent(ProducerPasswordResetMail::class, function ($mail) use ($producer): bool {
        parse_str(parse_url($mail->resetUrl, PHP_URL_FRAGMENT), $fragment);

        return $mail->hasTo($producer->email)
            && str_starts_with($mail->resetUrl, 'http://localhost:5174/password-reset/confirm#')
            && $fragment['email'] === $producer->email
            && PasswordResetToken::where('token_hash', hash('sha256', $fragment['token']))->exists();
    });
});

it('invalidates the prior link on resend and sets the configured expiry', function (): void {
    Mail::fake();
    $this->freezeTime();
    $producer = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    $this->postJson('/api/v1/producer/password-reset/start', ['email' => $producer->email])->assertAccepted();
    expect(PasswordResetToken::where('token_hash', hash('sha256', $payload['token']))->exists())->toBeFalse();
    expect(PasswordResetToken::sole()->expires_at)->toEqual(now()->addMinutes(30)->startOfSecond());
});

it('validates and resets once preserving exact password and revoking only own sessions', function (): void {
    $producer = User::factory()->producer()->create();
    $other = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    foreach ([$producer, $other] as $user) {
        DB::table('sessions')->insert(['id' => $user->id, 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
    }
    $this->postJson('/api/v1/producer/password-reset/validate', $payload)->assertOk()->assertJsonPath('data.valid', true);
    $this->actingAs($producer)->postJson('/api/v1/producer/password-reset/complete', $payload)->assertOk()->assertJsonPath('data.redirect', '/login');
    expect(Hash::check($payload['password'], $producer->refresh()->password))->toBeTrue();
    expect(Hash::check(trim($payload['password']), $producer->password))->toBeFalse();
    expect(DB::table('sessions')->where('user_id', $producer->id)->exists())->toBeFalse();
    expect(DB::table('sessions')->where('user_id', $other->id)->exists())->toBeTrue();
    $this->getJson('/api/v1/producer/me')->assertUnauthorized();
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable()->assertJsonPath('code', 'PASSWORD_RESET_TOKEN_INVALID');
    $this->postJson('/api/v1/producer/password-reset/validate', $payload)->assertUnprocessable();
    expect(AuditEvent::where('action', 'producer.password_reset')->count())->toBe(1);
    expect(AuditEvent::sole()->toJson())->not->toContain($payload['password'])->not->toContain($payload['token']);
});

it('rejects expired links at the boundary but accepts immediately before expiry', function (): void {
    $this->freezeTime();
    $producer = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    $this->travel(29)->minutes();
    $this->travel(59)->seconds();
    $this->postJson('/api/v1/producer/password-reset/validate', $payload)->assertOk();
    $this->travel(1)->seconds();
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable()->assertJsonPath('code', 'PASSWORD_RESET_TOKEN_INVALID');
});

it('rejects tokens from another role or email', function (string $role): void {
    $user = User::factory()->create(['role' => $role]);
    $payload = producerResetPayload($user);
    $this->postJson('/api/v1/producer/password-reset/validate', $payload)->assertUnprocessable();
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable();
})->with(['buyer', 'admin']);

it('rejects mismatched and unknown tokens without changing password', function (): void {
    $producer = User::factory()->producer()->create();
    $other = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    $payload['email'] = $other->email;
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable();
    $payload['email'] = $producer->email;
    $payload['token'] = str_repeat('z', 64);
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable();
    expect(PasswordResetToken::sole()->consumed_at)->toBeNull();
});

it('enforces password policy without consuming the token on validation failure', function (string $password): void {
    $producer = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    $payload['password'] = $payload['password_confirmation'] = $password;
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable()->assertJsonValidationErrors('password');
    expect(PasswordResetToken::sole()->consumed_at)->toBeNull();
})->with(['Aa1!abc', 'Aa1!'.str_repeat('a', 61), 'lowercase1!', 'UPPERCASE1!', 'NoDigits!!', 'NoSymbols1 ', 'Aa1漢字abc']);

it('accepts the password length boundaries', function (string $password): void {
    $producer = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    $payload['password'] = $payload['password_confirmation'] = $password;
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertOk();
})->with(['Aa1!abcd', 'Aa1!'.str_repeat('a', 60)]);

it('requires exact confirmation', function (): void {
    $producer = User::factory()->producer()->create();
    $payload = producerResetPayload($producer);
    $payload['password_confirmation'] = trim($payload['password']);
    $this->postJson('/api/v1/producer/password-reset/complete', $payload)->assertUnprocessable()->assertJsonValidationErrors('password_confirmation');
});

it('limits anonymous requests and prevents caching reset responses', function (): void {
    for ($i = 0; $i < 6; $i++) {
        $this->postJson('/api/v1/producer/password-reset/start', ['email' => 'unknown@example.test'])->assertAccepted()->assertHeader('Cache-Control', 'no-store, private');
    }
    $this->postJson('/api/v1/producer/password-reset/start', ['email' => 'unknown@example.test'])->assertStatus(429);
});

it('requires CSRF even without an Origin header', function (): void {
    $this->app['env'] = 'local';
    $this->withHeader('X-CSRF-TOKEN', 'wrong-token');
    $this->postJson('/api/v1/producer/password-reset/start', ['email' => 'unknown@example.test'])->assertStatus(419);
    $this->flushHeaders();
    $this->postJson('/api/v1/producer/password-reset/start', ['email' => 'unknown@example.test'])->assertStatus(419);
});

it('sends synchronously and preserves the neutral response when delivery fails', function (): void {
    $producer = User::factory()->producer()->create();
    $sendAttempts = 0;
    Event::listen(MessageSending::class, function () use (&$sendAttempts): void {
        $sendAttempts++;
        throw new RuntimeException('Synthetic mail transport failure');
    });
    $known = $this->postJson('/api/v1/producer/password-reset/start', ['email' => $producer->email])->assertAccepted()->json();
    $unknown = $this->postJson('/api/v1/producer/password-reset/start', ['email' => 'unknown@example.test'])->assertAccepted()->json();
    expect($known)->toBe($unknown);
    expect($sendAttempts)->toBe(1);
    expect(DB::table('jobs')->count())->toBe(0);
});

it('applies the same confirmed password policy to registration and reset', function (string $password, bool $valid): void {
    // FR-P-002/015, P02-01/P15-02, TBD-002, AT-P-002/015.
    foreach ([new CompleteProducerRegistrationRequest, new CompleteProducerPasswordResetRequest] as $request) {
        $rules = $request->rules();
        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $password],
            ['password' => $rules['password'], 'password_confirmation' => $rules['password_confirmation']],
        );
        expect($validator->passes())->toBe($valid);
    }
})->with([
    ['Aa1!abcd', true],
    ['Aa1★abcd', true],
    ['Aa1🌾abcd', true],
    ['Aa1漢字abc', false],
    ['Aa1 abcd', false],
    ['Aa1!'.str_repeat('🌾', 60), true],
    ['Aa1!'.str_repeat('🌾', 61), false],
]);
