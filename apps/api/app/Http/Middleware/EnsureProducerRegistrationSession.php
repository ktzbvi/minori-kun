<?php

namespace App\Http\Middleware;

use App\Models\ProducerRegistrationAttempt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProducerRegistrationSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie(config('producer_registration.cookie_name'));
        $attempt = is_string($token) && strlen($token) >= 32 && strlen($token) <= 128
            ? ProducerRegistrationAttempt::query()
                ->where('purpose', 'producer_registration')
                ->where('session_token_hash', hash('sha256', $token))
                ->first()
            : null;

        // Every domain operation independently checks expiry, ownership and consumption.
        $request->attributes->set('producer_registration_attempt', $attempt);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
