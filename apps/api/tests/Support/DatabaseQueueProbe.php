<?php

namespace Tests\Support;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

final class DatabaseQueueProbe implements ShouldQueue
{
    public function __construct(public string $cacheKey) {}

    public function handle(): void
    {
        Cache::store('database')->put($this->cacheKey, 'processed', 60);
    }
}
