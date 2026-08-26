<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $jumlahGuru = 10;

        // 1. Buat akun guru secara individual menggunakan Factory
        Guru::factory()->count($jumlahGuru)->create();

        $this->command->info("✅ GuruSeeder selesai. {$jumlahGuru} Akun Guru berhasil dibuat.");
    }
}
