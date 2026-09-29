<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\BuyerPasswordResetController;
use App\Http\Controllers\Api\BuyerRegistrationController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\PortalAuthController;
use Illuminate\Support\Facades\Route;

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
