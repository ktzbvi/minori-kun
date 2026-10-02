<?php

namespace App\Domain\Identity;

use App\Enums\AccountState;
use App\Enums\UserRole;
use App\Mail\ProducerPasswordResetMail;
use App\Models\AuditEvent;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProducerPasswordResetService
{
    private function producer(string $email): Builder
    {
        return User::query()->where('email', mb_strtolower(trim($email)))
            ->where('role', UserRole::Producer->value)->where('account_state', AccountState::Active->value);
    }

    public function start(string $email): void
    {
        DB::transaction(function () use ($email): void {
            $producer = $this->producer($email)->lockForUpdate()->first();
            if (! $producer) {
                return;
            }
            $token = Str::random(64);
            PasswordResetToken::query()->where('user_id', $producer->id)->delete();
            PasswordResetToken::query()->create([
                'user_id' => $producer->id, 'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addMinutes(config('producer-password-reset.expires_minutes')),
            ]);
            // Fragment credentials are not sent to the frontend server or in Referer headers.
            $url = rtrim(config('producer-password-reset.web_url'), '/').'/password-reset/confirm#'.http_build_query([
                'email' => $producer->email, 'token' => $token,
            ]);
            DB::afterCommit(fn () => Mail::to($producer->email)->queue(new ProducerPasswordResetMail($url)));
        });
    }

    public function valid(string $email, string $token): bool
    {
        $producer = $this->producer($email)->first();

        return $producer && $this->token($producer->id, $token)->exists();
    }

    private function token(string $id, string $token): Builder
    {
        return PasswordResetToken::query()->where('user_id', $id)->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')->where('expires_at', '>', now());
    }

    public function complete(string $email, string $token, string $password): ?string
    {
        return DB::transaction(function () use ($email, $token, $password): ?string {
            $producer = $this->producer($email)->lockForUpdate()->first();
            if (! $producer) {
                return null;
            }
            $reset = $this->token($producer->id, $token)->lockForUpdate()->first();
            if (! $reset) {
                return null;
            }
            $producer->forceFill(['password' => $password, 'password_changed_at' => now()])->save();
            $reset->update(['consumed_at' => now()]);
            DB::table('sessions')->where('user_id', $producer->id)->delete();
            AuditEvent::query()->create([
                'actor_id' => $producer->id, 'actor_role' => 'producer', 'target_type' => 'user',
                'target_id' => $producer->id, 'action' => 'producer.password_reset',
                'result' => 'success', 'occurred_at' => now(),
            ]);

            return $producer->id;
        });
    }
}
