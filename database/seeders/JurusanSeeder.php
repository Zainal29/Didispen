<?php
namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        Jurusan::insert([
            ['kode_jurusan' => 'RPL', 'nama_jurusan' => 'Rekayasa Perangkat Lunak', 'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'AKL', 'nama_jurusan' => 'Akutansi Keuangan Lembaga', 'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'MPLB', 'nama_jurusan' => 'Manajemen Perkantoran Lembaga Bisnis', 'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'PM', 'nama_jurusan' => 'Pemasaran', 'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'TO', 'nama_jurusan' => 'Teknik Otomotif', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
