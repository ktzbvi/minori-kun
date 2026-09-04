<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

final class DatabaseQueueProbe implements ShouldQueue
{
    public function __construct(public string $cacheKey) {}

    public function handle(): void
    {
        Cache::store('database')->put($this->cacheKey, 'processed', 60);
    }
}

it('provides the database-backed Laravel runtime tables', function (): void {
    foreach (['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Missing runtime table: {$table}");
    }
});

it('stores and removes cache values through the database store', function (): void {
    Cache::store('database')->put('runtime-cache-probe', 'ready', 60);

    expect(Cache::store('database')->get('runtime-cache-probe'))->toBe('ready');

    Cache::store('database')->forget('runtime-cache-probe');

    expect(Cache::store('database')->get('runtime-cache-probe'))->toBeNull();
});

it('queues and processes a database job exactly once', function (): void {
    config()->set('queue.default', 'database');
    config()->set('cache.default', 'database');

    Queue::connection('database')->push(new DatabaseQueueProbe('runtime-queue-probe'));

    expect(DB::table('jobs')->count())->toBe(1);

    Artisan::call('queue:work', ['--once' => true, '--queue' => 'default']);

    expect(DB::table('jobs')->count())->toBe(0)
        ->and(Cache::store('database')->get('runtime-queue-probe'))->toBe('processed');
});

it('dispatches database jobs only after a transaction commits', function (): void {
    expect(config('queue.connections.database.after_commit'))->toBeTrue();
});
