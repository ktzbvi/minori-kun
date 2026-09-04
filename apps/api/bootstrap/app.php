<?php

use App\Http\Middleware\EnsurePortalRole;
use App\Http\Middleware\EnsureProducerEligible;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'portal.role' => EnsurePortalRole::class,
            'producer.eligible' => EnsureProducerEligible::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => '入力内容を確認してください。',
                'code' => 'VALIDATION_ERROR',
                'errors' => $exception->errors(),
            ], 422);
        });
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => '認証が必要です。',
                'code' => 'UNAUTHENTICATED',
                'errors' => new stdClass,
            ], 401);
        });
    })->create();
