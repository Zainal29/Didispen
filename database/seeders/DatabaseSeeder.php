<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan WAJIB seperti ini agar tidak error:
        $this->call([
            JurusanSeeder::class,   // (Jika Anda punya file ini)
            KelasSeeder::class,     // (Jika Anda punya file ini)
            SiswaSeeder::class,     // Membuat User & Siswa (Zainal)
            GuruSeeder::class,      // Membuat User & Guru (Budi)
          // Membuat contoh dispensasi (Aman skip jika data kosong)
        ]);
    }
}
