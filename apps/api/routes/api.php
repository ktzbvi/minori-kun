<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\BuyerAccountController;
use App\Http\Controllers\Api\BuyerInquiryController;
use App\Http\Controllers\Api\BuyerPasswordResetController;
use App\Http\Controllers\Api\BuyerRegistrationController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PortalAuthController;
use App\Http\Controllers\Api\ProducerDashboardController;
use App\Http\Controllers\Api\ProducerOnboardingController;
use App\Http\Controllers\Api\ProducerProductController;
use App\Http\Controllers\Api\ProducerRegistrationController;
use App\Http\Middleware\EnsureProducerRegistrationSession;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::get('/health/live', [HealthController::class, 'live']);
Route::get('/health/ready', [HealthController::class, 'ready']);

Route::prefix('api/v1')->group(function (): void {
    Route::prefix('buyer')->group(function (): void {
        Route::prefix('registration')->group(function (): void {
            Route::post('/start', [BuyerRegistrationController::class, 'start']);
            Route::get('/status', [BuyerRegistrationController::class, 'status']);
            Route::post('/resend-otp', [BuyerRegistrationController::class, 'resend']);
            Route::post('/verify-otp', [BuyerRegistrationController::class, 'verify']);
            Route::post('/complete', [BuyerRegistrationController::class, 'complete']);
        });

        Route::prefix('password-reset')
            ->middleware('throttle:6,1')
            ->group(function (): void {
                Route::post('/start', [BuyerPasswordResetController::class, 'start']);
                Route::post('/complete', [BuyerPasswordResetController::class, 'complete']);
            });

        Route::get('/account/email-verifications/{token}', [BuyerAccountController::class, 'verifyEmailChange'])
            ->where('token', '[A-Za-z0-9]+');

        Route::middleware(['auth:sanctum', 'portal.role:buyer'])->group(function (): void {
            Route::prefix('account')->group(function (): void {
                Route::get('/profile', [BuyerAccountController::class, 'show']);
                Route::patch('/profile', [BuyerAccountController::class, 'update']);
                Route::put('/password', [BuyerAccountController::class, 'changePassword']);
            });

            Route::post('/inquiries', [BuyerInquiryController::class, 'store']);
        });
    });

    Route::prefix('producer')->group(function (): void {
        Route::get('/shop-photos/{id}', [ProducerRegistrationController::class, 'publicPhoto'])
            ->whereUlid('id')
            ->name('producer.shop-photo');

        // One unconditional cookie/session/CSRF stack, including requests without Origin.
        Route::middleware(['web', EnsureProducerRegistrationSession::class])
            ->withoutMiddleware(EnsureFrontendRequestsAreStateful::class)
            ->group(function (): void {
                Route::get('/registration', [ProducerRegistrationController::class, 'show']);
                Route::delete('/registration', [ProducerRegistrationController::class, 'destroy']);
                Route::post('/registration/code', [ProducerRegistrationController::class, 'requestCode']);
                Route::post('/registration/code/resend', [ProducerRegistrationController::class, 'resend']);
                Route::post('/registration/code/verify', [ProducerRegistrationController::class, 'verify']);
                Route::get('/registration/details', [ProducerRegistrationController::class, 'details']);
                Route::post('/registration/photo', [ProducerRegistrationController::class, 'uploadPhoto']);
                Route::get('/registration/photo/{id}', [ProducerRegistrationController::class, 'previewPhoto'])
                    ->whereUlid('id')
                    ->name('producer.registration.photo');
                Route::delete('/registration/photo/{id}', [ProducerRegistrationController::class, 'deletePhoto'])
                    ->whereUlid('id');
                Route::post('/registration/complete', [ProducerRegistrationController::class, 'complete']);
                Route::post('/registration/recover', [ProducerRegistrationController::class, 'recover']);
                Route::get('/terms', [ProducerRegistrationController::class, 'terms']);

                Route::get('/onboarding', [ProducerOnboardingController::class, 'show'])
                    ->middleware(['auth:sanctum', 'portal.role:producer']);
            });

        Route::middleware(['auth:sanctum', 'portal.role:producer', 'producer.eligible'])->group(function (): void {
            Route::get('/dashboard', [ProducerDashboardController::class, 'show']);
            Route::get('/products/options', [ProducerProductController::class, 'options']);
            Route::get('/products', [ProducerProductController::class, 'index']);
            Route::post('/products', [ProducerProductController::class, 'store']);
            Route::get('/products/{product}', [ProducerProductController::class, 'show'])->whereUlid('product');
            Route::post('/products/{product}', [ProducerProductController::class, 'update'])->whereUlid('product');
        });
    });

    foreach (UserRole::cases() as $role) {
        Route::prefix($role->value)->group(function () use ($role): void {
            Route::post('/auth/login', [PortalAuthController::class, 'login'])
                ->defaults('portal_role', $role->value)
                ->middleware('throttle:portal-login');

            Route::middleware(['auth:sanctum', "portal.role:{$role->value}"])->group(function (): void {
                Route::get('/me', [PortalAuthController::class, 'me']);
                Route::post('/auth/logout', [PortalAuthController::class, 'logout']);
            });
        });
    }
});
