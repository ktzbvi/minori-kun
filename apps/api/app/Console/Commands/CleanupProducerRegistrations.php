<?php

namespace App\Console\Commands;

use App\Domain\ProducerRegistration\ProducerRegistrationService;
use Illuminate\Console\Command;

class CleanupProducerRegistrations extends Command
{
    protected $signature = 'producer-registration:cleanup';

    protected $description = 'Remove expired, unclaimed Producer registration photos and temporary state';

    public function handle(ProducerRegistrationService $registration): int
    {
        $removedPhotos = $registration->cleanupExpiredPhotos();
        $this->info("Removed {$removedPhotos} expired Producer registration photos.");

        return self::SUCCESS;
    }
}
