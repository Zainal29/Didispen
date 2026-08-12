<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $jurusans = [
            [
                'kode_jurusan' => 'RPL',
                'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            ],
            [
                'kode_jurusan' => 'AKL',
                'nama_jurusan' => 'Akuntansi Keuangan Lembaga',
            ],
            [
                'kode_jurusan' => 'MPLB',
                'nama_jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis',
            ],
            [
                'kode_jurusan' => 'PM',
                'nama_jurusan' => 'Pemasaran',
            ],
            [
                'kode_jurusan' => 'TO',
                'nama_jurusan' => 'Teknik Otomotif',
            ],
        ];

        // ✅ Gunakan firstOrCreate agar tidak error jika data sudah ada
        foreach ($jurusans as $jurusanData) {
            Jurusan::firstOrCreate(
                ['kode_jurusan' => $jurusanData['kode_jurusan']], // Cek berdasarkan kode (kolom unique)
                $jurusanData                                       // Buat data baru jika kode belum ada
            );
        }

        $this->command->info('✅ JurusanSeeder selesai. Data jurusan aman dari duplikasi.');
    }
}