<?php

use App\Console\Commands\CheckOkeConnectOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-check OkeConnect processing orders every 5 minutes
Schedule::command(CheckOkeConnectOrders::class)->everyFiveMinutes();
