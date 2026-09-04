<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProducerEligible
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isSellingEligible()) {
            return new JsonResponse([
                'message' => '販売利用の審査が完了していません。',
                'code' => 'PRODUCER_NOT_ELIGIBLE',
                'errors' => new \stdClass,
            ], 403);
        }

        return $next($request);
    }
}
