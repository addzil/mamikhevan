<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:work database --stop-when-empty --max-time=50 --timeout=30 --tries=3 --backoff=60 --no-interaction')
    ->everyMinute()
    ->withoutOverlapping(5);
