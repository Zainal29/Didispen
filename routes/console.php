<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus otomatis audit log yang lebih dari 24 jam.
// Dijalankan tiap jam, jadi data maksimal bertahan ±25 jam.
Schedule::command('model:prune')->hourly();

// Jalankan setiap 15 menit setelah jam 15:00
Schedule::command('dispensasi:auto-complete')
    ->dailyAt('15:15')
    ->withoutOverlapping();

// Atau jalankan setiap 15 menit dari jam 15:00 - 17:00
Schedule::command('dispensasi:auto-complete')
    ->between('15:00', '17:00')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
