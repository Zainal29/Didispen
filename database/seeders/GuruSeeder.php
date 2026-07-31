<?php
namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        Guru::insert([
            ['user_id' => 2, 'nip' => '1987654321', 'nama_lengkap' => 'Budi Santoso', 'mata_pelajaran' => 'Matematika', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 3, 'nip' => '1987654322', 'nama_lengkap' => 'Siti Rahayu', 'mata_pelajaran' => 'Bahasa Inggris', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
