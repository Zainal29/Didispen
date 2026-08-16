<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SatpamSeeder::class,      // (Opsional) Tambahkan agar akun satpam terbuat
            JurusanSeeder::class,
            KelasSeeder::class,
            GuruSeeder::class,
            // GuruPiketSeeder::class,   // 🔥 TAMBAHKAN INI: Wajib jalan sebelum DispensasiSeeder
            SiswaSeeder::class,
            SettingsSeeder::class,
            DispensasiSeeder::class,  // Sekarang ini akan aman!
        ]);
    }
}