<?php

use App\Mail\BuyerSupportInquiryMail;
use App\Models\BuyerInquiry;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    config()->set('mail.support_address', 'support@example.test');
});

it('accepts an inquiry from an authenticated buyer', function (): void {
    $buyer = User::factory()->buyer()->create();

    $this->actingAs($buyer)
        ->postJson('/api/v1/buyer/inquiries', [
            'subject' => 'Delivery question',
            'message' => 'Please tell me the expected delivery date.',
            'idempotency_key' => '4e9d476d-33ba-49ec-83e4-60a1d1d0ecde',
        ])
        ->assertCreated()
        ->assertJsonPath('data.reference_number', fn (string $reference): bool => str_starts_with($reference, 'INQ-'));

    expect(BuyerInquiry::query()->where('buyer_id', $buyer->id)->count())->toBe(1);
    Mail::assertSent(BuyerSupportInquiryMail::class, function (BuyerSupportInquiryMail $mail): bool {
        return $mail->hasTo('support@example.test')
            && $mail->referenceNumber !== ''
            && str_contains($mail->render(), 'Delivery question');
    });
});

it('returns the original inquiry when the same request is retried', function (): void {
    $buyer = User::factory()->buyer()->create();
    $payload = [
        'subject' => 'Delivery question',
        'message' => 'Please tell me the expected delivery date.',
        'idempotency_key' => 'a1ee4f6e-2991-4b1c-997e-0c9d45ca7e66',
    ];

    $first = $this->actingAs($buyer)
        ->postJson('/api/v1/buyer/inquiries', $payload)
        ->assertCreated();

    $this->actingAs($buyer)
        ->postJson('/api/v1/buyer/inquiries', $payload)
        ->assertOk()
        ->assertJsonPath('data.reference_number', $first->json('data.reference_number'));

    expect(BuyerInquiry::query()->where('buyer_id', $buyer->id)->count())->toBe(1);
});

it('requires an inquiry subject and message', function (): void {
    $buyer = User::factory()->buyer()->create();

    $this->actingAs($buyer)
        ->postJson('/api/v1/buyer/inquiries', [
            'subject' => '',
            'message' => '',
            'idempotency_key' => '0cdf03eb-6b9d-45d1-8520-957d28e2dd62',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['subject', 'message']);
});
