<?php
namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 50 Siswa (sekaligus akan membuat 50 akun User secara otomatis di belakang layar)
        Siswa::factory()->count(50)->create();
        
        $this->command->info('✅ SiswaSeeder selesai. (50 Siswa & Akun User dinamis berhasil dibuat)');
    }
}