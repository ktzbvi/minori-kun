<?php

namespace App\Providers;

use App\Contracts\PayjpMarketplaceGateway;
use App\Infrastructure\Payjp\OfficialPayjpMarketplaceGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PayjpMarketplaceGateway::class, OfficialPayjpMarketplaceGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('portal-login', function (Request $request): Limit {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
