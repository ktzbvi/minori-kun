<?php

use App\Enums\ProducerOperationalState;
use App\Enums\ScreeningState;
use App\Models\AuditEvent;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerProfile;
use App\Models\ProducerRegistrationPhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function accountSettingsProducer(): User
{
    $user = User::factory()->producer()->create();
    ProducerProfile::query()->create(['user_id' => $user->id, 'farm_name' => 'テスト農園', 'operational_state' => ProducerOperationalState::Active, 'selling_eligible_at' => now()]);
    $tenant = PayjpTenant::query()->create(['producer_id' => $user->id, 'provider_tenant_reference' => 'ten_'.$user->id, 'application_state' => 'approved', 'bank_state' => 'registered']);
    foreach (['visa', 'mastercard'] as $brand) {
        PayjpScreening::query()->create(['tenant_id' => $tenant->id, 'card_brand' => $brand, 'state' => ScreeningState::Passed]);
    }

    return $user;
}

// FR-P-011 / P11-02/04 / DATA-002 / SEC-002 / AT-P-011.
it('returns only the signed in Producer summary with a masked email', function (): void {
    $producer = accountSettingsProducer();
    $this->actingAs($producer)->getJson('/api/v1/producer/account')->assertOk()
        ->assertJsonPath('data.farm_name', 'テスト農園')->assertJsonPath('data.shop_photo_url', null)
        ->assertJsonMissing(['email' => $producer->email])->assertHeader('Cache-Control', 'no-store, private');
    $this->actingAs(User::factory()->buyer()->create())->getJson('/api/v1/producer/account')->assertNotFound();
});

it('confirms normalized photo replacement and treats repeated uploads idempotently', function (): void {
    Storage::fake('local');
    $producer = accountSettingsProducer();
    $other = accountSettingsProducer();
    $file = UploadedFile::fake()->image('photo.png', 1600, 800);
    $this->actingAs($producer)->postJson('/api/v1/producer/account/photo', ['photo' => $file, 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertOk();
    $first = $producer->producerProfile->fresh()->shop_photo_id;
    $photo = ProducerRegistrationPhoto::query()->findOrFail($first);
    expect($photo->width)->toBe(1024);
    expect($photo->height)->toBe(512);
    expect($other->producerProfile->fresh()->shop_photo_id)->toBeNull();
    $this->postJson('/api/v1/producer/account/photo', ['photo' => $file, 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertOk();
    expect($producer->producerProfile->fresh()->shop_photo_id)->toBe($first);
    expect(AuditEvent::query()->where('action', 'producer.shop_photo.updated')->count())->toBe(1);
    $this->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('replacement.png', 80, 80), 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertOk();
    expect($producer->producerProfile->fresh()->shop_photo_id)->not->toBe($first);
    Storage::disk('local')->assertMissing($photo->storage_path);
    $this->get('/api/v1/producer/shop-photos/'.$first)->assertNotFound();
});

it('preserves the previous photo for invalid uploads and denied roles', function (): void {
    Storage::fake('local');
    $producer = accountSettingsProducer();
    $this->actingAs($producer)->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('photo.png'), 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertOk();
    $photo = $producer->producerProfile->fresh()->shop_photo_id;
    foreach ([UploadedFile::fake()->create('bad.txt', 1, 'text/plain'), UploadedFile::fake()->image('too-wide.png', 4097, 1), UploadedFile::fake()->create('large.png', 5121, 'image/png')] as $file) {
        $this->postJson('/api/v1/producer/account/photo', ['photo' => $file, 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertUnprocessable();
        expect($producer->producerProfile->fresh()->shop_photo_id)->toBe($photo);
    }
    $this->actingAs(User::factory()->buyer()->create())->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('foreign.png'), 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertNotFound();
    expect($producer->producerProfile->fresh()->shop_photo_id)->toBe($photo);
});

it('keeps the prior photo and audit state when storage fails', function (): void {
    Storage::fake('local');
    $producer = accountSettingsProducer();
    $this->actingAs($producer)->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('first.png', 80, 80), 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertOk();
    $photoId = $producer->producerProfile->fresh()->shop_photo_id;
    $photo = ProducerRegistrationPhoto::query()->findOrFail($photoId);
    $disk = Mockery::mock(Storage::disk('local'))->makePartial();
    $disk->shouldReceive('put')->andReturnFalse();
    Storage::set('local', $disk);
    $this->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('next.png', 160, 160), 'expected_photo_id' => $producer->producerProfile->fresh()->shop_photo_id])->assertUnprocessable();
    expect($producer->producerProfile->fresh()->shop_photo_id)->toBe($photoId);
    expect($photo->fresh()->deleted_at)->toBeNull();
    expect(AuditEvent::query()->where('action', 'producer.shop_photo.updated')->count())->toBe(1);
    expect(Storage::disk('local')->exists($photo->storage_path))->toBeTrue();
});

it('requires authentication and selling eligibility for account settings', function (): void {
    $this->getJson('/api/v1/producer/account')->assertUnauthorized();
    $this->postJson('/api/v1/producer/account/photo')->assertUnauthorized();
    $this->actingAs(User::factory()->producer()->create())->getJson('/api/v1/producer/account')->assertForbidden();
});

it('ends the Producer session when logout is confirmed', function (): void {
    $this->withHeader('Origin', 'http://localhost:5174');
    $producer = accountSettingsProducer();
    $producer->update(['password' => Hash::make('ValidPassword!123')]);
    $this->postJson('/api/v1/producer/auth/login', ['email' => $producer->email, 'password' => 'ValidPassword!123'])->assertOk();
    $this->getJson('/api/v1/producer/account')->assertOk();
    $this->postJson('/api/v1/producer/auth/logout')->assertOk()->assertJsonPath('data.logged_out', true);
    $this->getJson('/api/v1/producer/account')->assertUnauthorized();
});

// P11-04 / AT-P-011: delayed retry A must not replace a subsequent successful B.
it('rejects delayed and stale photo changes while allowing immediate replay', function (): void {
    Storage::fake('local');
    $producer = accountSettingsProducer();
    $a = UploadedFile::fake()->image('a.png', 80, 80);
    $b = UploadedFile::fake()->image('b.png', 160, 160);
    $this->actingAs($producer)->postJson('/api/v1/producer/account/photo', ['photo' => $a, 'expected_photo_id' => null])->assertOk();
    $aId = $producer->producerProfile->fresh()->shop_photo_id;
    $this->postJson('/api/v1/producer/account/photo', ['photo' => $a, 'expected_photo_id' => null])->assertOk();
    $this->postJson('/api/v1/producer/account/photo', ['photo' => $b, 'expected_photo_id' => $aId])->assertOk();
    $bId = $producer->producerProfile->fresh()->shop_photo_id;
    $this->postJson('/api/v1/producer/account/photo', ['photo' => $a, 'expected_photo_id' => null])->assertConflict();
    $this->postJson('/api/v1/producer/account/photo', ['photo' => $a, 'expected_photo_id' => $aId])->assertConflict();
    expect($producer->producerProfile->fresh()->shop_photo_id)->toBe($bId);
    expect(AuditEvent::query()->where('action', 'producer.shop_photo.updated')->count())->toBe(2);
    expect(Storage::disk('local')->allFiles('producer-shop-photos'))->toHaveCount(1);
});

it('requires a valid expected photo reference for confirmed changes', function (): void {
    Storage::fake('local');
    $producer = accountSettingsProducer();
    $this->actingAs($producer)->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('photo.png')])->assertUnprocessable()->assertJsonValidationErrors('expected_photo_id');
    $this->postJson('/api/v1/producer/account/photo', ['photo' => UploadedFile::fake()->image('photo.png'), 'expected_photo_id' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('expected_photo_id');
    expect($producer->producerProfile->fresh()->shop_photo_id)->toBeNull();
});
