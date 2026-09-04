<?php

use App\Enums\ProductPublicationState;
use App\Models\PayjpScreening;
use App\Models\PayjpTenant;
use App\Models\ProducerProfile;
use App\Models\Product;
use App\Models\User;

it('prevents one producer from managing another producers product', function (): void {
    $owner = User::factory()->producer()->create();
    $other = User::factory()->producer()->create();
    $product = Product::factory()->create(['producer_id' => $owner->id]);

    expect($other->can('update', $product))->toBeFalse()
        ->and($owner->can('update', $product))->toBeTrue();
});

it('allows buyers to view only published products', function (): void {
    $buyer = User::factory()->buyer()->create();
    $producer = User::factory()->producer()->create();
    $draft = Product::factory()->create(['producer_id' => $producer->id]);
    $published = Product::factory()->create(['producer_id' => $producer->id, 'publication_state' => ProductPublicationState::Published]);

    expect($buyer->can('view', $draft))->toBeFalse()
        ->and($buyer->can('view', $published))->toBeTrue();
});

it('requires both Visa and Mastercard screening passes for selling eligibility', function (): void {
    $producer = User::factory()->producer()->create();
    ProducerProfile::query()->create(['user_id' => $producer->id, 'farm_name' => 'Test Farm', 'operational_state' => 'active', 'selling_eligible_at' => now()]);
    $tenant = PayjpTenant::query()->create(['producer_id' => $producer->id, 'provider_tenant_reference' => 'ten_test', 'application_state' => 'approved', 'bank_state' => 'registered']);
    PayjpScreening::query()->create(['tenant_id' => $tenant->id, 'card_brand' => 'visa', 'state' => 'passed']);

    expect($producer->fresh()->isSellingEligible())->toBeFalse();

    PayjpScreening::query()->create(['tenant_id' => $tenant->id, 'card_brand' => 'mastercard', 'state' => 'passed']);
    expect($producer->fresh()->isSellingEligible())->toBeTrue();
});
