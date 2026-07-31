<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            JurusanSeeder::class,
            KelasSeeder::class,
            SiswaSeeder::class,
            GuruSeeder::class,
            GuruPiketSeeder::class,
            SettingsSeeder::class,
            DispensasiSeeder::class,
        ]);
    }
}
