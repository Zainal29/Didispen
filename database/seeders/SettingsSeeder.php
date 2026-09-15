<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting; // Pastikan model Setting disesuaikan dengan nama model kamu

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'print_start_time', 'value' => '06:00'],
            ['key' => 'print_end_time', 'value' => '17:00'],
            ['key' => 'print_max_limit', 'value' => '3'],
        ];

        foreach ($settings as $setting) {
            // updateOrCreate akan mengecek:
            // Jika 'key' sudah ada, maka update 'value'-nya. Jika belum, buat baru.
            Setting::updateOrCreate(
                ['key' => $setting['key']],     // Parameter pencarian (Unique)
                ['value' => $setting['value']]  // Data yang diisi/diupdate
            );
        }

        // Menampilkan pesan sukses di terminal (opsional)
        $this->command->info('✅ SettingsSeeder selesai. Data setting aman dari duplikasi.');
    }
}