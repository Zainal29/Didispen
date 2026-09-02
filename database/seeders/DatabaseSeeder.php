<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,        // 1. Admin Akun
            SatpamSeeder::class,      // 2. Satpam Akun
            JurusanSeeder::class,     // 3. Jurusan Manual
            KelasSeeder::class,       // 4. Kelas Manual
            GuruSeeder::class,        // 5. Guru Manual
            SiswaSeeder::class,       // 6. Siswa Manual
            SettingsSeeder::class,    // 7. Pengaturan Sistem
            DispensasiSeeder::class,  // 8. Contoh Dispensasi
        ]);
    }
}
