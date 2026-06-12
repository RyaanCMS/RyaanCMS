<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

// RGIN — Global Intelligence Network auto push/sync (opt-in, configurable time)
if (config('rgin.auto_push')) {
    Schedule::command('intelligence:push --force --limit=100')
        ->dailyAt(config('rgin.auto_push_time', '03:00'))
        ->withoutOverlapping()
        ->runInBackground();
}

if (config('rgin.auto_sync')) {
    Schedule::command('intelligence:sync')
        ->dailyAt(config('rgin.auto_sync_time', '04:00'))
        ->withoutOverlapping()
        ->runInBackground();
}
