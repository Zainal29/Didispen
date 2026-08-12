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