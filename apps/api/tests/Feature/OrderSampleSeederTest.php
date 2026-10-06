<?php

use App\Models\AuditEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProducerOrder;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OrderSampleSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

// FR-P-008 / SCR-P-008 / AT-P-008 / DATA-006 / SEC-002.
it('seeds visible owned order snapshots and preserves them on replay without external effects', function (): void {
    Storage::fake('public');
    Http::fake();
    Mail::fake();
    $this->seed(DatabaseSeeder::class);
    $producer = User::query()->where('email', 'producer@example.test')->firstOrFail();
    $this->actingAs($producer)->getJson('/api/v1/producer/orders')
        ->assertOk()->assertJsonPath('meta.total', 4)
        ->assertJsonPath('data.0.operational_state', 'received')
        ->assertJsonPath('data.1.operational_state', 'confirmed')
        ->assertJsonPath('data.2.operational_state', 'processing')
        ->assertJsonPath('data.3.operational_state', 'shipped');
    $original = Order::query()->orderBy('id')->get()->toArray();
    ProducerOrder::query()->where('fulfillment_state', 'shipped')->update(['fulfillment_state' => 'processing']);
    $this->seed(OrderSampleSeeder::class);
    expect(Order::query()->orderBy('id')->get()->toArray())->toBe($original);
    expect(ProducerOrder::query()->where('fulfillment_state', 'shipped')->count())->toBe(0);
    expect(OrderItem::query()->count())->toBe(4);
    expect(AuditEvent::query()->where('action', 'sample_order.seeded')->count())->toBe(4);
    foreach (ProducerOrder::query()->with(['order.deliveryAddress', 'items'])->get() as $record) {
        expect($record->producer_id)->toBe($producer->id);
        expect($record->order->deliveryAddress)->not->toBeNull();
        expect($record->total_yen)->toBe($record->order->total_yen);
        foreach ($record->items as $item) {
            expect($item->producer_id)->toBe($producer->id);
            expect($item->quantity)->toBe(1);
        }
    }
    $other = User::factory()->buyer()->create();
    $this->actingAs($other)->getJson('/api/v1/producer/orders/'.$record->id)->assertNotFound();
    Http::assertNothingSent();
    Mail::assertNothingSent();
});

it('rejects production and missing product prerequisites without creating sample orders', function (): void {
    app()->instance('env', 'production');
    expect(fn () => (new OrderSampleSeeder)->run())->toThrow(RuntimeException::class);
    expect(Order::query()->count())->toBe(0);
    app()->instance('env', 'testing');
    expect(fn () => (new OrderSampleSeeder)->run())->toThrow(ModelNotFoundException::class);
    expect(Order::query()->count())->toBe(0);
});
