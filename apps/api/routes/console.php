<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('producer-registration:cleanup')->hourly()->withoutOverlapping();
Schedule::command('buyer:dispatch-refund-email-outbox')->everyMinute()->withoutOverlapping();
Schedule::call(fn () => DB::table('orders')
    ->where('order_state', '注文確定')
    ->where('payment_state', 'succeeded')
    ->where('cancellation_deadline_at', '<=', now())
    ->update(['order_state' => '完了']))
    ->everyMinute()
    ->name('orders:complete-expired')
    ->withoutOverlapping();
