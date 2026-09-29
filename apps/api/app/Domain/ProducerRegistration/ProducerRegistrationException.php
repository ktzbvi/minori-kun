<?php

namespace App\Domain\ProducerRegistration;

use RuntimeException;

class ProducerRegistrationException extends RuntimeException
{
    /**
     * @param array<string, list<string>> $errors
     * @param array<string, mixed> $context Safe response metadata only.
     */
    public function __construct(
        public readonly string $errorCode,
        public readonly int $status = 422,
        public readonly array $errors = [],
        public readonly array $context = [],
        public readonly ?string $cookieToken = null,
        ?string $message = null,
    ) {
        parent::__construct($message ?? '登録内容を確認して、もう一度お試しください。');
    }
}
