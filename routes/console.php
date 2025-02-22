<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

// Run migrations
Schedule::command('migrate:check')->everyMinute()->withoutOverlapping();

// Schedule run workers
Schedule::command('queue:work', ['--stop-when-empty', '--no-interaction', '--max-jobs=2'])->everyMinute();
