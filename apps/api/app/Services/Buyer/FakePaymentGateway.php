<?php

namespace App\Services\Buyer;

use RuntimeException;

class FakePaymentGateway
{
    public function charge(int $amountYen, string $idempotencyKey): array
    {
        if (! app()->environment(['local', 'testing']) || config('buyer_payment.driver') !== 'fake') {
            throw new RuntimeException('Fake payment is disabled outside local and testing environments.');
        }

        return [
            'state' => 'succeeded',
            'reference' => 'fake_'.substr(hash('sha256', $idempotencyKey), 0, 32),
            'amount_yen' => $amountYen,
        ];
    }

    public function refund(int $amountYen, string $idempotencyKey): array
    {
        if (! app()->environment(['local', 'testing']) || config('buyer_payment.driver') !== 'fake') {
            throw new RuntimeException('Fake refunds are disabled outside local and testing environments.');
        }

        return [
            'state' => 'refunded',
            'reference' => 'fake_refund_'.substr(hash('sha256', $idempotencyKey), 0, 24),
            'amount_yen' => $amountYen,
        ];
    }
}
