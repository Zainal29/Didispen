<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SatpamSeeder::class,
            JurusanSeeder::class,     // ⚠️ Wajib sebelum KelasSeeder & SiswaSeeder
            KelasSeeder::class,       // ⚠️ Wajib sebelum SiswaSeeder
            GuruSeeder::class,
            SiswaSeeder::class,
            SettingsSeeder::class,
            DispensasiSeeder::class,
        ]);
    }
}
