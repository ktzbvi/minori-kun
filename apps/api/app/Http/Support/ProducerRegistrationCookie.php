<?php

namespace App\Http\Support;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

final class ProducerRegistrationCookie
{
    public static function queue(string $token, DateTimeInterface|string $expiresAt, Request $request): void
    {
        Cookie::queue(Cookie::make(
            config('producer_registration.cookie_name'),
            $token,
            1440,
            '/',
            null,
            app()->isProduction() || $request->isSecure(),
            true,
            false,
            'lax',
        )->withExpires($expiresAt));
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(config('producer_registration.cookie_name'), '/'));
    }
}
