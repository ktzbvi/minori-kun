<?php

namespace App\Infrastructure\Payjp;

use App\Contracts\PayjpMarketplaceGateway;
use Payjp\Payjp;
use Payjp\Tenant;
use RuntimeException;

class OfficialPayjpMarketplaceGateway implements PayjpMarketplaceGateway
{
    public function __construct()
    {
        $secret = (string) config('services.payjp.secret');
        if ($secret === '') {
            throw new RuntimeException('PAY.JP credentials are not configured.');
        }
        Payjp::setApiKey($secret);
    }

    public function createTenant(array $attributes): array
    {
        $tenant = Tenant::create($attributes);

        return ['id' => (string) $tenant->id];
    }

    public function createApplicationUrl(string $tenantReference, string $returnTo): array
    {
        $tenant = Tenant::retrieve($tenantReference);
        $applicationUrl = $tenant->application_urls->create(['return_to' => $returnTo]);

        return ['url' => (string) $applicationUrl->url];
    }

    public function retrieveTenant(string $tenantReference): array
    {
        return Tenant::retrieve($tenantReference)->toArray();
    }
}
