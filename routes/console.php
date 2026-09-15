<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Membersihkan model yang menggunakan trait Prunable (jika ada)
Schedule::command('model:prune')->hourly();

// Auto-complete dispensasi yang terlambat (15:00 - 17:00, setiap 15 menit)
Schedule::command('dispensasi:auto-complete')
    ->between('15:00', '17:00')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Hapus riwayat dispensasi lama (30 hari) + FOTO-nya otomatis terhapus via Model Hook
Schedule::command('dispensasi:cleanup-history')->dailyAt('00:00');

// Backup otomatis pembersihan foto verifikasi & bukti untuk dispensasi yang sudah selesai
Schedule::command('dispensasi:cleanup-foto')->dailyAt('02:00');
