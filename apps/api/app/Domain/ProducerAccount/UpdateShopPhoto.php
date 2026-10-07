<?php

namespace App\Domain\ProducerAccount;

use App\Domain\ProducerRegistration\ProducerRegistrationPhotoProcessor;
use App\Models\AuditEvent;
use App\Models\ProducerProfile;
use App\Models\ProducerRegistrationPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class UpdateShopPhoto
{
    // P11-04 / DATA-002: decode with the confirmed registration photo policy.
    public function execute(User $producer, UploadedFile $file, ?string $expectedPhotoId): User
    {
        $processed = (new ProducerRegistrationPhotoProcessor)->process($file);
        $path = 'producer-shop-photos/'.Str::ulid().'.'.$processed->extension;
        $oldPath = null;
        try {
            DB::transaction(function () use ($producer, $processed, $path, $expectedPhotoId, &$oldPath): void {
                $profile = ProducerProfile::query()->where('user_id', $producer->id)->lockForUpdate()->firstOrFail();
                $old = $profile->shop_photo_id ? ProducerRegistrationPhoto::query()->whereKey($profile->shop_photo_id)->lockForUpdate()->firstOrFail() : null;
                if ($old?->storage_path && Storage::disk('local')->exists($old->storage_path)
                    && hash_equals(hash('sha256', Storage::disk('local')->get($old->storage_path)), hash('sha256', $processed->bytes))) {
                    return; // Replayed confirmation of the same normalized image is a no-op.
                }
                if ($profile->shop_photo_id !== $expectedPhotoId) {
                    throw new ConflictHttpException('プロフィール写真が更新されています。再読み込みして、もう一度写真を選択してください。');
                }
                if (! Storage::disk('local')->put($path, $processed->bytes)) {
                    throw new \RuntimeException('Photo storage failed.');
                }
                if ($old) {
                    $oldPath = $old->storage_path;
                    $old->update(['claimed_by_producer_id' => null, 'deleted_at' => now()]);
                }
                $photo = ProducerRegistrationPhoto::query()->create([
                    'storage_path' => $path, 'mime_type' => $processed->mimeType,
                    'width' => $processed->width, 'height' => $processed->height, 'size_bytes' => $processed->sizeBytes,
                    'claimed_by_producer_id' => $producer->id, 'claimed_at' => now(), 'expires_at' => now()->addDay(),
                ]);
                $profile->update(['shop_photo_id' => $photo->id]);
                AuditEvent::query()->create([
                    'actor_id' => $producer->id, 'target_type' => 'producer_profile', 'target_id' => $profile->id,
                    'action' => 'producer.shop_photo.updated', 'result' => 'success', 'occurred_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            // A unique candidate path cannot remove another request's committed photo.
            try {
                Storage::disk('local')->delete($path);
            } catch (Throwable) {
            }
            if ($exception instanceof HttpExceptionInterface) {
                throw $exception;
            }
            report(new \RuntimeException('Producer shop photo update failed.'));
            throw ValidationException::withMessages(['photo' => ['写真を保存できませんでした。もう一度お試しください。']]);
        }
        if ($oldPath) {
            // Cleanup happens only after the profile swap commits; cleanup failure must not undo success.
            try {
                Storage::disk('local')->delete($oldPath);
            } catch (Throwable) {
                report(new \RuntimeException('Superseded shop photo cleanup failed.'));
            }
        }

        return $producer->fresh('producerProfile');
    }
}
