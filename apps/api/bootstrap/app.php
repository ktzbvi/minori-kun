<?php

use App\Http\Middleware\EnsurePortalRole;
use App\Http\Middleware\EnsureProducerEligible;
use App\Domain\ProducerRegistration\ProducerRegistrationException;
use App\Http\Support\ProducerRegistrationCookie;
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
        $exceptions->dontFlash(['code']);
        $exceptions->dontReport([ProducerRegistrationException::class]);
        $exceptions->render(function (ProducerRegistrationException $exception, Request $request) {
            if ($exception->cookieToken !== null) {
                ProducerRegistrationCookie::queue(
                    $exception->cookieToken,
                    $exception->context['registration_state']['session_expires_at'],
                    $request,
                );
            }

            return response()->json([
                'message' => $exception->getMessage(),
                'code' => $exception->errorCode,
                'errors' => $exception->errors ?: new stdClass,
                ...$exception->context,
            ], $exception->status)->header('Cache-Control', 'no-store, private');
        });
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if (request()->is('api/v1/producer/registration*', 'api/v1/producer/terms', 'api/v1/producer/onboarding')) {
                $response->headers->set('Cache-Control', 'no-store, private');
            }

            return $response;
        });
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
