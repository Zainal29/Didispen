<?php
namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        Siswa::insert([
            ['user_id' => 4, 'jurusan_id' => 1, 'kelas_id' => 1, 'nama_lengkap' => 'Ahmad Fauzi', 'tanggal_lahir' => '2007-05-10', 'alamat' => 'Jl. Merdeka No. 10, Jakarta', 'no_telepon' => '081234567890', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 5, 'jurusan_id' => 2, 'kelas_id' => 2, 'nama_lengkap' => 'Dewi Lestari', 'tanggal_lahir' => '2008-02-15', 'alamat' => 'Jl. Sudirman No. 5, Bandung', 'no_telepon' => '081298765432', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 6, 'jurusan_id' => 3, 'kelas_id' => 3, 'nama_lengkap' => 'Rizky Pratama', 'tanggal_lahir' => '2006-12-01', 'alamat' => 'Jl. Diponegoro No. 20, Surabaya', 'no_telepon' => '081356789012', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
