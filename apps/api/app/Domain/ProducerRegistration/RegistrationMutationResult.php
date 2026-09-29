<?php

namespace App\Domain\ProducerRegistration;

use App\Models\ProducerRegistrationAttempt;

final readonly class RegistrationMutationResult
{
    /**
     * @param array{state:string,email:?string,server_time:string,otp_expires_at:?string,resend_available_at:?string,session_expires_at:?string,delivery_succeeded:?bool} $state
     */
    public function __construct(
        public ProducerRegistrationAttempt $attempt,
        public array $state,
        public ?string $cookieToken = null,
    ) {}
}
