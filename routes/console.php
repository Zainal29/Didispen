<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('model:prune')->hourly();

// ✅ OPSI B: Jalankan setiap 15 menit dari jam 15:00 - 17:00
Schedule::command('dispensasi:auto-complete')
    ->between('15:00', '17:00')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Jalankan setiap hari jam 00:00
Schedule::command('dispensasi:cleanup-history')->dailyAt('00:00');
Schedule::command('dispensasi:cleanup-foto')->dailyAt('02:00');
