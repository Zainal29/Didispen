<?php
namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        Kelas::insert([
            ['jurusan_id' => 1, 'nama_kelas' => 'X RPL 1', 'tingkat' => 'X', 'created_at' => now(), 'updated_at' => now()],
            ['jurusan_id' => 2, 'nama_kelas' => 'XI TKJ 2', 'tingkat' => 'XI', 'created_at' => now(), 'updated_at' => now()],
            ['jurusan_id' => 3, 'nama_kelas' => 'XII MM 1', 'tingkat' => 'XII', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
