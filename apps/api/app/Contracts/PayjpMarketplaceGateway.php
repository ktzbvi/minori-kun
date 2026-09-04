<?php

namespace App\Contracts;

interface PayjpMarketplaceGateway
{
    /** @return array{id:string} */
    public function createTenant(array $attributes): array;

    /** @return array{url:string} */
    public function createApplicationUrl(string $tenantReference, string $returnTo): array;

    /** @return array<string, mixed> */
    public function retrieveTenant(string $tenantReference): array;
}
