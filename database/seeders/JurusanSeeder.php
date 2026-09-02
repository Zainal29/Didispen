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
                'kode_jurusan' => 'PPLG',
                'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            ],
            [
                'kode_jurusan' => 'AKL',
                'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',
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

        foreach ($jurusans as $item) {
            Jurusan::firstOrCreate(
                ['kode_jurusan' => $item['kode_jurusan']],
                $item
            );
        }

        $this->command->info('✅ JurusanSeeder selesai. (5 Jurusan manual berhasil disiapkan)');
    }
}
