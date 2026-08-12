<?php
namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 10 Kelas secara acak
        Kelas::factory()->count(10)->create();
        
        $this->command->info('✅ KelasSeeder selesai. (10 Kelas dinamis berhasil dibuat)');
    }
}