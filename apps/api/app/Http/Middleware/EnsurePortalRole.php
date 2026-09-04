<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $expected = UserRole::tryFrom($role);

        if (! $expected || ! $request->user()?->isRole($expected)) {
            abort(404);
        }

        return $next($request);
    }
}
