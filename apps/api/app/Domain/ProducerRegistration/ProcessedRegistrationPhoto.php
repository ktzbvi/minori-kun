<?php

namespace App\Domain\ProducerRegistration;

final readonly class ProcessedRegistrationPhoto
{
    public function __construct(
        public string $bytes,
        public string $mimeType,
        public int $width,
        public int $height,
        public int $sizeBytes,
        public string $extension,
    ) {}
}
