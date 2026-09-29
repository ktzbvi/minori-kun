<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\BuyerPasswordResetController;
use App\Http\Controllers\Api\BuyerRegistrationController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PortalAuthController;
use App\Http\Controllers\Api\ProducerOnboardingController;
use App\Http\Controllers\Api\ProducerRegistrationController;
use App\Http\Middleware\EnsureProducerRegistrationSession;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::get('/health/live', [HealthController::class, 'live']);
Route::get('/health/ready', [HealthController::class, 'ready']);

Route::prefix('/api/v1/buyer/registration')->group(function (): void {
    Route::post('/start', [BuyerRegistrationController::class, 'start']);
    Route::get('/status', [BuyerRegistrationController::class, 'status']);
    Route::post('/resend-otp', [BuyerRegistrationController::class, 'resend']);
    Route::post('/verify-otp', [BuyerRegistrationController::class, 'verify']);
    Route::post('/complete', [BuyerRegistrationController::class, 'complete']);
});

Route::prefix('/api/v1/buyer/password-reset')->middleware('throttle:6,1')->group(function (): void {
    Route::post('/start', [BuyerPasswordResetController::class, 'start']);
    Route::post('/complete', [BuyerPasswordResetController::class, 'complete']);
});

// One unconditional cookie/session/CSRF stack, including requests without Origin.
Route::prefix('api/v1/producer')
    ->middleware(['web', EnsureProducerRegistrationSession::class])
    ->withoutMiddleware(EnsureFrontendRequestsAreStateful::class)
    ->group(function (): void {
        Route::get('/registration', [ProducerRegistrationController::class, 'show']);
        Route::post('/registration/code', [ProducerRegistrationController::class, 'requestCode']);
        Route::post('/registration/code/resend', [ProducerRegistrationController::class, 'resend']);
        Route::post('/registration/code/verify', [ProducerRegistrationController::class, 'verify']);
        Route::delete('/registration', [ProducerRegistrationController::class, 'destroy']);
        Route::get('/registration/details', [ProducerRegistrationController::class, 'details']);
        Route::post('/registration/photo', [ProducerRegistrationController::class, 'uploadPhoto']);
        Route::get('/registration/photo/{id}', [ProducerRegistrationController::class, 'previewPhoto'])
            ->whereUlid('id')->name('producer.registration.photo');
        Route::delete('/registration/photo/{id}', [ProducerRegistrationController::class, 'deletePhoto'])->whereUlid('id');
        Route::post('/registration/complete', [ProducerRegistrationController::class, 'complete']);
        Route::post('/registration/recover', [ProducerRegistrationController::class, 'recover']);
        Route::get('/terms', [ProducerRegistrationController::class, 'terms']);
        Route::get('/onboarding', [ProducerOnboardingController::class, 'show'])
            ->middleware(['auth:sanctum', 'portal.role:producer']);
    });

Route::get('/api/v1/producer/shop-photos/{id}', [ProducerRegistrationController::class, 'publicPhoto'])
    ->whereUlid('id')->name('producer.shop-photo');

foreach (UserRole::cases() as $role) {
    Route::prefix("api/v1/{$role->value}")->group(function () use ($role): void {
        Route::post('/auth/login', [PortalAuthController::class, 'login'])
            ->defaults('portal_role', $role->value)
            ->middleware('throttle:portal-login');

        Route::middleware(['auth:sanctum', "portal.role:{$role->value}"])->group(function (): void {
            Route::get('/me', [PortalAuthController::class, 'me']);
            Route::post('/auth/logout', [PortalAuthController::class, 'logout']);
        });
    });
}
