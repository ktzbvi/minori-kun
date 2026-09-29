<?php

namespace App\Domain\ProducerRegistration;

use App\Enums\AccountState;
use App\Enums\ProducerOperationalState;
use App\Enums\UserRole;
use App\Mail\ProducerRegistrationOtpMail;
use App\Models\ProducerProfile;
use App\Models\ProducerRegistrationAttempt;
use App\Models\ProducerRegistrationPhoto;
use App\Models\ProducerTermsAcceptance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Throwable;

class ProducerRegistrationService
{
    /** @return array{state:string,email:?string,server_time:string,otp_expires_at:?string,resend_available_at:?string,session_expires_at:?string,delivery_succeeded:?bool} */
    public function state(?ProducerRegistrationAttempt $attempt): array
    {
        $now = now();
        $state = 'none';
        $sessionExpiry = null;
        $resendAvailableAt = null;

        if ($attempt !== null && $attempt->invalidated_at === null) {
            if ($attempt->consumed_at !== null) {
                $state = 'consumed';
            } elseif ($attempt->verified_at !== null) {
                $sessionExpiry = $attempt->grant_expires_at;
                $state = $sessionExpiry !== null && $sessionExpiry->isFuture() ? 'verified' : 'expired';
            } else {
                $sessionExpiry = $attempt->expires_at;
                $state = $sessionExpiry !== null && $sessionExpiry->isFuture() ? 'pending' : 'expired';
            }

            if ($attempt->email !== null) {
                $resendAvailableAt = DB::table('producer_registration_email_cooldowns')
                    ->where('email', $attempt->email)
                    ->value('resend_available_at');
            }
        }

        return [
            'state' => $state,
            'email' => $attempt !== null && $attempt->invalidated_at === null ? $attempt->email : null,
            'server_time' => $now->toIso8601String(),
            'otp_expires_at' => $attempt?->otp_expires_at?->toIso8601String(),
            'resend_available_at' => $this->iso($resendAvailableAt),
            'session_expires_at' => $this->iso($sessionExpiry),
            'delivery_succeeded' => $attempt?->delivery_succeeded,
        ];
    }

    public function requestCode(?ProducerRegistrationAttempt $attempt, string $email): RegistrationMutationResult
    {
        $email = mb_strtolower(trim($email));
        $token = null;
        $code = null;
        $mailRecipient = null;
        $generation = null;
        $cooldownFailure = null;
        $photosToDelete = [];

        $result = DB::transaction(function () use ($attempt, $email, &$token, &$code, &$mailRecipient, &$generation, &$cooldownFailure, &$photosToDelete): ?ProducerRegistrationAttempt {
            $locked = $this->lockMatchingAttempt($attempt);
            $sameEmail = $locked !== null && $locked->email === $email;
            $isActive = $locked !== null && $locked->consumed_at === null && $locked->invalidated_at === null;
            $cooldownReserved = false;
            $activeExpiry = $locked?->verified_at !== null ? $locked->grant_expires_at : $locked?->expires_at;

            if ($isActive && $sameEmail && $locked->verified_at !== null && $locked->grant_expires_at?->isFuture()) {
                return $locked;
            }

            if (! $isActive || $activeExpiry === null || ! $activeExpiry->isFuture()) {
                $cooldown = $this->lockCooldown($email);
                $availableAt = CarbonImmutable::parse($cooldown->resend_available_at);
                if ($availableAt->isFuture()) {
                    $cooldownFailure = $this->cooldownContext($availableAt);

                    return null;
                }
                $cooldownUntil = now()->addSeconds((int) config('producer_registration.resend_cooldown_seconds'));
                DB::table('producer_registration_email_cooldowns')->where('email', $email)->update([
                    'resend_available_at' => $cooldownUntil,
                    'last_requested_at' => now(),
                    'updated_at' => now(),
                ]);
                $cooldownReserved = true;
                if ($locked !== null) {
                    $photosToDelete = array_merge($photosToDelete, $this->markAttemptPhotosForDeletion($locked));
                    $locked->forceFill(['invalidated_at' => now()])->save();
                }
                $token = bin2hex(random_bytes(32));
                $locked = ProducerRegistrationAttempt::query()->create([
                    'purpose' => 'producer_registration',
                    'session_token_hash' => hash('sha256', $token),
                    'email' => $email,
                    'otp_generation' => 0,
                    'expires_at' => now()->addMinutes((int) config('producer_registration.pending_ttl_minutes')),
                ]);
                $sameEmail = false;
            }

            if (! $sameEmail) {
                $photosToDelete = array_merge($photosToDelete, $this->markAttemptPhotosForDeletion($locked));
                $locked->forceFill([
                    'email' => $email,
                    'otp_hmac' => null,
                    'otp_expires_at' => null,
                    'otp_consumed_at' => null,
                    'delivery_succeeded' => null,
                    'otp_delivered_at' => null,
                    'verified_at' => null,
                    'grant_expires_at' => null,
                    'expires_at' => now()->addMinutes((int) config('producer_registration.pending_ttl_minutes')),
                    'current_photo_id' => null,
                ])->save();
            }

            if (! $cooldownReserved) {
                $cooldown = $this->lockCooldown($email);
                $availableAt = CarbonImmutable::parse($cooldown->resend_available_at);
                if ($availableAt->isFuture()) {
                    $cooldownFailure = $this->cooldownContext($availableAt);

                    return $locked->fresh();
                }
                $cooldownUntil = now()->addSeconds((int) config('producer_registration.resend_cooldown_seconds'));
                DB::table('producer_registration_email_cooldowns')->where('email', $email)->update([
                    'resend_available_at' => $cooldownUntil,
                    'last_requested_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $generation = (int) $locked->otp_generation + 1;
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $locked->forceFill([
                'otp_hmac' => $this->otpHmac($locked->id, $email, $generation, $code),
                'otp_generation' => $generation,
                'verification_attempts' => 0,
                'resend_count' => (int) $locked->resend_count + 1,
                'otp_expires_at' => now()->addSeconds((int) config('producer_registration.otp_ttl_seconds')),
                'otp_consumed_at' => null,
                'delivery_succeeded' => null,
                'otp_delivered_at' => null,
                'verified_at' => null,
                'grant_expires_at' => null,
            ])->save();
            $mailRecipient = $email;

            return $locked->fresh();
        }, 3);

        foreach (array_unique($photosToDelete) as $photoId) {
            $this->cleanupUnclaimedPhoto((string) $photoId);
        }

        if ($cooldownFailure !== null) {
            throw new ProducerRegistrationException(
                'RESEND_COOLDOWN', 429, [], $cooldownFailure + ['registration_state' => $this->state($result)], $token,
                '確認コードを再送できる時刻までお待ちください。',
            );
        }

        if ($mailRecipient !== null && $code !== null && $generation !== null) {
            $this->deliverCode($result, $mailRecipient, $code, $generation, $token);
            $result = $result->fresh();
        }

        return new RegistrationMutationResult($result, $this->state($result), $token);
    }

    public function resend(?ProducerRegistrationAttempt $attempt): RegistrationMutationResult
    {
        $cooldownFailure = null;
        $code = null;
        $email = null;
        $generation = null;
        $result = DB::transaction(function () use ($attempt, &$cooldownFailure, &$code, &$email, &$generation): ProducerRegistrationAttempt {
            $locked = $this->requireLockedAttempt($attempt);
            $this->assertPending($locked);
            if ($locked->verified_at !== null && $locked->grant_expires_at?->isFuture()) {
                return $locked;
            }
            if ($locked->email === null) {
                throw $this->problem('REGISTRATION_REQUIRED', 409);
            }

            $cooldown = $this->lockCooldown($locked->email);
            $availableAt = CarbonImmutable::parse($cooldown->resend_available_at);
            if ($availableAt->isFuture()) {
                $cooldownFailure = $this->cooldownContext($availableAt);

                return $locked->fresh();
            }

            $cooldownUntil = now()->addSeconds((int) config('producer_registration.resend_cooldown_seconds'));
            DB::table('producer_registration_email_cooldowns')->where('email', $locked->email)->update([
                'resend_available_at' => $cooldownUntil,
                'last_requested_at' => now(),
                'updated_at' => now(),
            ]);

            $generation = (int) $locked->otp_generation + 1;
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $email = $locked->email;
            $locked->forceFill([
                'otp_hmac' => $this->otpHmac($locked->id, $email, $generation, $code),
                'otp_generation' => $generation,
                'verification_attempts' => 0,
                'resend_count' => (int) $locked->resend_count + 1,
                'otp_expires_at' => now()->addSeconds((int) config('producer_registration.otp_ttl_seconds')),
                'otp_consumed_at' => null,
                'delivery_succeeded' => null,
                'otp_delivered_at' => null,
                'verified_at' => null,
                'grant_expires_at' => null,
            ])->save();
            return $locked->fresh();
        }, 3);

        if ($cooldownFailure !== null) {
            throw new ProducerRegistrationException(
                'RESEND_COOLDOWN', 429, [], $cooldownFailure + ['registration_state' => $this->state($result)], null,
                '確認コードを再送できる時刻までお待ちください。',
            );
        }

        if ($email !== null && $code !== null && $generation !== null) {
            $this->deliverCode($result, $email, $code, $generation, null);
            $result = $result->fresh();
        }

        return new RegistrationMutationResult($result, $this->state($result));
    }

    public function verify(?ProducerRegistrationAttempt $attempt, string $code): RegistrationMutationResult
    {
        $rotatedToken = bin2hex(random_bytes(32));
        $failure = null;
        $result = DB::transaction(function () use ($attempt, $code, $rotatedToken, &$failure): ProducerRegistrationAttempt {
            $locked = $this->requireLockedAttempt($attempt);
            if ($locked->consumed_at !== null) {
                throw $this->problem('REGISTRATION_CONSUMED', 409);
            }
            if ($locked->verified_at !== null && $locked->grant_expires_at?->isFuture()) {
                return $locked;
            }
            if ($locked->verified_at !== null || ! $locked->expires_at->isFuture()) {
                throw $this->problem('REGISTRATION_EXPIRED', 409);
            }
            if ($locked->email === null || $locked->otp_consumed_at !== null) {
                throw $this->problem('OTP_INVALID', 422, ['code' => ['確認コードを確認してください。']]);
            }
            if ($locked->otp_expires_at === null || $locked->otp_expires_at->isPast()) {
                throw $this->problem('OTP_EXPIRED', 422, ['code' => ['確認コードの有効期限が切れました。']]);
            }

            $expected = $this->otpHmac($locked->id, $locked->email, (int) $locked->otp_generation, $code);
            if ($locked->otp_hmac === null || ! hash_equals($locked->otp_hmac, $expected)) {
                $locked->increment('verification_attempts');
                $failure = $this->problem('OTP_INVALID', 422, ['code' => ['確認コードを確認してください。']]);

                return $locked->fresh();
            }

            $grantExpiresAt = now()->addMinutes((int) config('producer_registration.verified_ttl_minutes'));
            $locked->forceFill([
                'session_token_hash' => hash('sha256', $rotatedToken),
                'otp_consumed_at' => now(),
                'verified_at' => now(),
                'grant_expires_at' => $grantExpiresAt,
            ])->save();

            return $locked->fresh();
        }, 3);

        if ($failure !== null) {
            throw $failure;
        }

        $newCookie = $result->verified_at !== null && $result->session_token_hash === hash('sha256', $rotatedToken)
            ? $rotatedToken
            : null;

        return new RegistrationMutationResult($result, $this->state($result), $newCookie);
    }

    /** @return array{version:string,title:string,content:string,is_sample:bool} */
    public function terms(): array
    {
        return $this->configuredTerms();
    }

    /** @return array{email:string,terms:array{version:string,title:string,content:string,is_sample:bool},photo:?array{id:string,preview_url:string,expires_at:string},session_expires_at:string} */
    public function details(?ProducerRegistrationAttempt $attempt): array
    {
        $valid = $this->requireVerified($attempt);
        $terms = $this->configuredTerms();
        $photo = null;
        if ($valid->current_photo_id !== null) {
            $staged = ProducerRegistrationPhoto::query()
                ->whereKey($valid->current_photo_id)
                ->where('attempt_id', $valid->id)
                ->whereNull('claimed_at')
                ->whereNull('deleted_at')
                ->where('expires_at', '>', now())
                ->first();
            if ($staged !== null) {
                $photo = [
                    'id' => $staged->id,
                    'preview_url' => route('producer.registration.photo', ['id' => $staged->id]),
                    'expires_at' => $staged->expires_at->toIso8601String(),
                ];
            }
        }

        return [
            'email' => (string) $valid->email,
            'terms' => $terms,
            'photo' => $photo,
            'session_expires_at' => $valid->grant_expires_at->toIso8601String(),
        ];
    }

    public function uploadPhoto(?ProducerRegistrationAttempt $attempt, UploadedFile $file): ProducerRegistrationPhoto
    {
        $valid = $this->requireVerified($attempt);
        $processed = (new ProducerRegistrationPhotoProcessor)->process($file);
        $photoId = (string) Str::ulid();
        $relativePath = 'producer-registration/'.$photoId.'.'.$processed->extension;
        if (! Storage::disk('local')->put($relativePath, $processed->bytes)) {
            throw $this->problem('PHOTO_UNAVAILABLE', 503, [], [], '写真を保存できませんでした。もう一度お試しください。');
        }

        $oldPhotoId = null;
        try {
            $photo = DB::transaction(function () use ($attempt, $valid, $photoId, $relativePath, $processed, &$oldPhotoId): ProducerRegistrationPhoto {
                $locked = $this->requireLockedAttempt($attempt);
                $this->assertVerifiedGrant($locked);
                if ($locked->id !== $valid->id) {
                    throw $this->problem('REGISTRATION_REQUIRED', 401);
                }
                if ($locked->current_photo_id !== null) {
                    $old = ProducerRegistrationPhoto::query()->whereKey($locked->current_photo_id)
                        ->where('attempt_id', $locked->id)->whereNull('claimed_at')->lockForUpdate()->first();
                    if ($old !== null) {
                        $oldPhotoId = $old->id;
                        $old->forceFill(['deleted_at' => now()])->save();
                    }
                }

                $photo = ProducerRegistrationPhoto::query()->create([
                    'id' => $photoId,
                    'attempt_id' => $locked->id,
                    'storage_path' => $relativePath,
                    'mime_type' => $processed->mimeType,
                    'width' => $processed->width,
                    'height' => $processed->height,
                    'size_bytes' => $processed->sizeBytes,
                    'expires_at' => now()->addMinutes((int) config('producer_registration.photo_ttl_minutes')),
                ]);
                $locked->forceFill(['current_photo_id' => $photo->id])->save();

                return $photo;
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($relativePath);
            if ($exception instanceof ProducerRegistrationException) {
                throw $exception;
            }
            throw $this->problem('PHOTO_UNAVAILABLE', 503, [], [], '写真を保存できませんでした。もう一度お試しください。');
        }

        if ($oldPhotoId !== null) {
            $this->cleanupUnclaimedPhoto($oldPhotoId);
        }

        return $photo;
    }

    public function photoForPreview(?ProducerRegistrationAttempt $attempt, string $photoId): ProducerRegistrationPhoto
    {
        $valid = $this->requireVerified($attempt);
        $photo = ProducerRegistrationPhoto::query()
            ->whereKey($photoId)
            ->where('attempt_id', $valid->id)
            ->whereNull('claimed_at')
            ->whereNull('deleted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($photo === null || $photo->storage_path === null || ! Storage::disk('local')->exists($photo->storage_path)) {
            throw $this->problem('PHOTO_UNAVAILABLE', 404, [], [], '写真を表示できません。もう一度アップロードしてください。');
        }

        return $photo;
    }

    public function deletePhoto(?ProducerRegistrationAttempt $attempt, string $photoId): void
    {
        $id = DB::transaction(function () use ($attempt, $photoId): ?string {
            $locked = $this->requireLockedAttempt($attempt);
            $this->assertVerifiedGrant($locked);
            $photo = ProducerRegistrationPhoto::query()
                ->whereKey($photoId)->where('attempt_id', $locked->id)
                ->whereNull('claimed_at')->lockForUpdate()->first();
            if ($photo === null) {
                throw $this->problem('PHOTO_UNAVAILABLE', 404);
            }
            $photo->forceFill(['deleted_at' => now()])->save();
            if ($locked->current_photo_id === $photo->id) {
                $locked->forceFill(['current_photo_id' => null])->save();
            }

            return $photo->id;
        }, 3);

        if ($id !== null) {
            $this->cleanupUnclaimedPhoto($id);
        }
    }

    public function cancel(?ProducerRegistrationAttempt $attempt): void
    {
        if ($attempt === null) {
            return;
        }

        $ids = DB::transaction(function () use ($attempt): array {
            $locked = $this->lockMatchingAttempt($attempt);
            if ($locked === null || $locked->consumed_at !== null) {
                return [];
            }
            $photos = ProducerRegistrationPhoto::query()->where('attempt_id', $locked->id)
                ->whereNull('claimed_at')->lockForUpdate()->get();
            foreach ($photos as $photo) {
                $photo->forceFill(['deleted_at' => now()])->save();
            }
            $locked->forceFill([
                'session_token_hash' => hash('sha256', random_bytes(32)),
                'email' => null,
                'otp_hmac' => null,
                'otp_expires_at' => null,
                'verified_at' => null,
                'grant_expires_at' => null,
                'invalidated_at' => now(),
                'current_photo_id' => null,
            ])->save();

            return $photos->pluck('id')->all();
        }, 3);

        foreach ($ids as $id) {
            $this->cleanupUnclaimedPhoto((string) $id);
        }
    }

    /** @param array<string, mixed> $input */
    public function complete(?ProducerRegistrationAttempt $attempt, array $input): User
    {
        try {
            return DB::transaction(function () use ($attempt, $input): User {
                $locked = $this->requireLockedAttempt($attempt);
                if ($locked->consumed_at !== null) {
                    throw $this->problem('REGISTRATION_CONSUMED', 409);
                }
                $this->assertVerifiedGrant($locked);

                $terms = $this->configuredTerms();
                if ($input['terms_version'] !== $terms['version']) {
                    throw $this->problem('TERMS_CHANGED', 409, [], ['terms' => $terms], '規約が更新されました。内容を確認して同意してください。');
                }

                if (User::query()->whereRaw('LOWER(email) = ?', [$locked->email])->exists()) {
                    throw $this->problem('ACCOUNT_EXISTS', 409, [], [], '登録を完了できませんでした。ログインまたは確認をやり直してください。');
                }

                $photo = ProducerRegistrationPhoto::query()
                    ->whereKey($input['photo_id'])
                    ->where('attempt_id', $locked->id)
                    ->whereNull('claimed_by_producer_id')
                    ->whereNull('claimed_at')
                    ->whereNull('deleted_at')
                    ->where('expires_at', '>', now())
                    ->lockForUpdate()->first();
                if ($photo === null || $photo->storage_path === null || ! Storage::disk('local')->exists($photo->storage_path)) {
                    throw $this->problem('PHOTO_UNAVAILABLE', 422, ['photo_id' => ['写真をアップロードし直してください。']]);
                }

                $producer = User::query()->create([
                    'role' => UserRole::Producer->value,
                    'email' => $locked->email,
                    'password' => $input['password'],
                    'account_state' => AccountState::Active->value,
                    'email_verified_at' => now(),
                ]);
                ProducerProfile::query()->create([
                    'user_id' => $producer->id,
                    'farm_name' => $input['shop_name'],
                    'contact_name' => $input['contact_name'],
                    'phone' => $input['phone'],
                    'operational_state' => ProducerOperationalState::Onboarding->value,
                    'shop_photo_id' => $photo->id,
                ]);
                ProducerTermsAcceptance::query()->create([
                    'producer_id' => $producer->id,
                    'terms_version' => $terms['version'],
                    'accepted_at' => now(),
                ]);
                $photo->forceFill([
                    'attempt_id' => null,
                    'claimed_by_producer_id' => $producer->id,
                    'claimed_at' => now(),
                ])->save();
                $locked->forceFill([
                    'consumed_at' => now(),
                    'resulting_producer_id' => $producer->id,
                    'otp_hmac' => null,
                    'email' => null,
                    'delivery_succeeded' => null,
                    'otp_delivered_at' => null,
                ])->save();

                return $producer;
            }, 3);
        } catch (QueryException $exception) {
            if (isset($attempt->email) && User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $attempt->email)])->exists()) {
                throw $this->problem('ACCOUNT_EXISTS', 409, [], [], '登録を完了できませんでした。ログインまたは確認をやり直してください。');
            }
            throw $exception;
        }
    }

    public function recoverCompleted(?ProducerRegistrationAttempt $attempt): User
    {
        return DB::transaction(function () use ($attempt): User {
            $locked = $this->requireLockedAttempt($attempt);
            if ($locked->consumed_at === null
                || $locked->resulting_producer_id === null
                || $locked->grant_expires_at === null
                || ! $locked->grant_expires_at->isFuture()) {
                throw $this->problem('REGISTRATION_CONSUMED', 409);
            }

            $producer = User::query()
                ->whereKey($locked->resulting_producer_id)
                ->where('role', UserRole::Producer->value)
                ->first();

            if ($producer === null) {
                throw $this->problem('REGISTRATION_CONSUMED', 409);
            }

            return $producer;
        }, 3);
    }

    public function cleanupExpiredPhotos(): int
    {
        $deleted = 0;
        $photoIds = ProducerRegistrationPhoto::query()
            ->whereNull('claimed_at')
            ->where(function ($query): void {
                $query->whereNotNull('deleted_at')->orWhere('expires_at', '<=', now());
            })
            ->pluck('id');

        foreach ($photoIds as $photoId) {
            $before = ProducerRegistrationPhoto::query()->whereKey($photoId)->exists();
            $this->cleanupUnclaimedPhoto((string) $photoId);
            if ($before && ! ProducerRegistrationPhoto::query()->whereKey($photoId)->exists()) {
                $deleted++;
            }
        }

        ProducerRegistrationAttempt::query()
            ->where(function ($query): void {
                $query->whereNotNull('invalidated_at')
                    ->orWhere(function ($expiredPending): void {
                        $expiredPending->whereNull('verified_at')->where('expires_at', '<=', now());
                    })
                    ->orWhere(function ($expiredGrant): void {
                        $expiredGrant->whereNotNull('verified_at')->where('grant_expires_at', '<=', now());
                    });
            })
            ->delete();

        DB::table('producer_registration_email_cooldowns')
            ->where('resend_available_at', '<=', now())
            ->delete();

        $disk = Storage::disk('local');
        foreach ($disk->files('producer-registration') as $path) {
            try {
                $isTracked = ProducerRegistrationPhoto::query()->where('storage_path', $path)->exists();
                if (! $isTracked && $disk->lastModified($path) <= now()->subHours(24)->getTimestamp()) {
                    $disk->delete($path);
                }
            } catch (Throwable) {
                // Leave the file in place so the next hourly cleanup can retry.
            }
        }

        return $deleted;
    }

    /** @return list<string> */
    private function markAttemptPhotosForDeletion(ProducerRegistrationAttempt $attempt): array
    {
        $photos = ProducerRegistrationPhoto::query()
            ->where('attempt_id', $attempt->id)
            ->whereNull('claimed_at')
            ->lockForUpdate()
            ->get();

        foreach ($photos as $photo) {
            if ($photo->deleted_at === null) {
                $photo->forceFill(['deleted_at' => now()])->save();
            }
        }

        return $photos->pluck('id')->map(static fn ($id): string => (string) $id)->all();
    }

    private function cleanupUnclaimedPhoto(string $photoId): void
    {
        $candidate = ProducerRegistrationPhoto::query()->whereKey($photoId)->whereNull('claimed_at')->first();
        if ($candidate === null || ($candidate->deleted_at === null && $candidate->expires_at->isFuture())) {
            return;
        }

        $path = $candidate->storage_path;
        try {
            if ($path !== null && Storage::disk('local')->exists($path) && ! Storage::disk('local')->delete($path)) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        DB::transaction(function () use ($photoId, $candidate, $path): void {
            if ($candidate->attempt_id !== null) {
                ProducerRegistrationAttempt::query()->whereKey($candidate->attempt_id)->lockForUpdate()->first();
            }
            $photo = ProducerRegistrationPhoto::query()->whereKey($photoId)->whereNull('claimed_at')->lockForUpdate()->first();
            if ($photo === null || $photo->storage_path !== $path) {
                return;
            }
            if ($photo->attempt_id !== null) {
                ProducerRegistrationAttempt::query()
                    ->whereKey($photo->attempt_id)
                    ->where('current_photo_id', $photo->id)
                    ->update(['current_photo_id' => null, 'updated_at' => now()]);
            }
            $photo->delete();
        }, 3);
    }

    private function deliverCode(ProducerRegistrationAttempt $attempt, string $email, string $code, int $generation, ?string $cookieToken): void
    {
        try {
            $mailer = (string) config('mail.default');
            $mailerConfig = config('mail.mailers.'.$mailer);
            if (! is_array($mailerConfig) || ($mailerConfig['transport'] ?? null) !== 'smtp') {
                throw new \RuntimeException('Registration email requires the direct SMTP transport.');
            }

            Mail::mailer($mailer)->to($email)->send(new ProducerRegistrationOtpMail($code));

            ProducerRegistrationAttempt::query()->whereKey($attempt->id)
                ->where('otp_generation', $generation)
                ->where('email', $email)
                ->update(['delivery_succeeded' => true, 'otp_delivered_at' => now(), 'updated_at' => now()]);
        } catch (Throwable) {
            $current = DB::transaction(function () use ($attempt, $email, $generation): ?ProducerRegistrationAttempt {
                $locked = ProducerRegistrationAttempt::query()->whereKey($attempt->id)->lockForUpdate()->first();
                if ($locked === null || $locked->otp_generation !== $generation || $locked->email !== $email) {
                    return $locked;
                }
                $locked->forceFill([
                    'otp_hmac' => null,
                    'delivery_succeeded' => false,
                    'otp_delivered_at' => null,
                ])->save();

                return $locked->fresh();
            }, 3);

            $state = $this->state($current ?? $attempt);
            throw new ProducerRegistrationException(
                'DELIVERY_FAILED', 503, [], [
                    'server_time' => now()->toIso8601String(),
                    'resend_available_at' => $state['resend_available_at'],
                    'registration_state' => $state,
                ], $cookieToken,
                '確認コードを送信できませんでした。時間をおいて再送してください。',
            );
        }
    }

    private function lockMatchingAttempt(?ProducerRegistrationAttempt $attempt): ?ProducerRegistrationAttempt
    {
        if ($attempt === null) {
            return null;
        }

        return ProducerRegistrationAttempt::query()
            ->whereKey($attempt->id)
            ->where('purpose', 'producer_registration')
            ->where('session_token_hash', $attempt->session_token_hash)
            ->lockForUpdate()
            ->first();
    }

    private function requireLockedAttempt(?ProducerRegistrationAttempt $attempt): ProducerRegistrationAttempt
    {
        $locked = $this->lockMatchingAttempt($attempt);
        if ($locked === null || $locked->invalidated_at !== null) {
            throw $this->problem('REGISTRATION_REQUIRED', 401);
        }

        return $locked;
    }

    private function requireVerified(?ProducerRegistrationAttempt $attempt): ProducerRegistrationAttempt
    {
        $locked = $this->requireLockedAttempt($attempt);
        if ($locked->consumed_at !== null) {
            throw $this->problem('REGISTRATION_CONSUMED', 409);
        }
        $this->assertVerifiedGrant($locked);

        return $locked;
    }

    private function assertPending(ProducerRegistrationAttempt $attempt): void
    {
        if ($attempt->consumed_at !== null) {
            throw $this->problem('REGISTRATION_CONSUMED', 409);
        }
        if ($attempt->verified_at !== null && ! $attempt->grant_expires_at?->isFuture()) {
            throw $this->problem('REGISTRATION_EXPIRED', 409);
        }
        if ($attempt->verified_at === null && ! $attempt->expires_at->isFuture()) {
            throw $this->problem('REGISTRATION_EXPIRED', 409);
        }
    }

    private function assertVerifiedGrant(ProducerRegistrationAttempt $attempt): void
    {
        if ($attempt->consumed_at !== null) {
            throw $this->problem('REGISTRATION_CONSUMED', 409);
        }
        if ($attempt->verified_at === null) {
            throw $this->problem('REGISTRATION_UNVERIFIED', 403);
        }
        if ($attempt->grant_expires_at === null || ! $attempt->grant_expires_at->isFuture()) {
            throw $this->problem('REGISTRATION_EXPIRED', 409);
        }
    }

    private function lockCooldown(string $email): object
    {
        $now = now();
        DB::table('producer_registration_email_cooldowns')->insertOrIgnore([
            'email' => $email,
            'resend_available_at' => $now,
            'last_requested_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return DB::table('producer_registration_email_cooldowns')->where('email', $email)->lockForUpdate()->first();
    }

    /** @return array{server_time:string,resend_available_at:string,retry_after:int} */
    private function cooldownContext(mixed $resendAvailableAt): array
    {
        $available = CarbonImmutable::parse($resendAvailableAt);

        return [
            'server_time' => now()->toIso8601String(),
            'resend_available_at' => $available->toIso8601String(),
            'retry_after' => max(1, now()->diffInSeconds($available, false)),
        ];
    }

    private function otpHmac(string $attemptId, string $email, int $generation, string $code): string
    {
        return hash_hmac('sha256', implode('|', ['producer_registration', $attemptId, $generation, $email, $code]), (string) config('app.key'));
    }

    /** @param array<string, list<string>> $errors @param array<string, mixed> $context */
    private function problem(string $code, int $status = 422, array $errors = [], array $context = [], ?string $message = null): ProducerRegistrationException
    {
        $defaultMessages = [
            'REGISTRATION_REQUIRED' => '登録セッションを確認できません。最初からやり直してください。',
            'REGISTRATION_EXPIRED' => '登録セッションの有効期限が切れました。確認コードを再送してください。',
            'REGISTRATION_UNVERIFIED' => '先にメールアドレスを確認してください。',
            'REGISTRATION_CONSUMED' => 'この登録セッションはすでに使用されています。',
            'OTP_INVALID' => '確認コードを確認してください。',
            'OTP_EXPIRED' => '確認コードの有効期限が切れました。再送してください。',
            'PHOTO_INVALID' => '写真ファイルを確認してください。',
            'PHOTO_UNAVAILABLE' => '写真を表示または保存できません。もう一度お試しください。',
            'TERMS_CHANGED' => '規約が更新されました。内容を確認して同意してください。',
            'TERMS_UNAVAILABLE' => '現在、規約を表示できません。時間をおいて再度お試しください。',
            'ACCOUNT_EXISTS' => '登録を完了できませんでした。ログインまたは確認をやり直してください。',
        ];

        return new ProducerRegistrationException($code, $status, $errors, $context, null, $message ?? ($defaultMessages[$code] ?? null));
    }

    /** @return array{version:string,title:string,content:string,is_sample:bool} */
    private function configuredTerms(): array
    {
        $terms = config('producer_registration.terms');
        if (! is_array($terms)
            || ! is_string($terms['version'] ?? null)
            || trim($terms['version']) === ''
            || mb_strlen($terms['version']) > 64
            || ! is_string($terms['title'] ?? null)
            || trim($terms['title']) === ''
            || ! is_string($terms['content'] ?? null)
            || trim($terms['content']) === '') {
            throw $this->problem('TERMS_UNAVAILABLE', 503);
        }

        $isSample = (bool) ($terms['is_sample'] ?? false);
        if (app()->isProduction() && $isSample) {
            throw $this->problem('TERMS_UNAVAILABLE', 503);
        }

        return [
            'version' => $terms['version'],
            'title' => $terms['title'],
            'content' => $terms['content'],
            'is_sample' => $isSample,
        ];
    }

    private function iso(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ($value instanceof \DateTimeInterface ? CarbonImmutable::instance($value) : CarbonImmutable::parse($value))->toIso8601String();
    }
}

