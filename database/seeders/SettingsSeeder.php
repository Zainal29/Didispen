<?php
namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::insert([
            ['key' => 'print_start_time', 'value' => '06:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'print_end_time', 'value' => '17:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'print_max_limit', 'value' => '3', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
