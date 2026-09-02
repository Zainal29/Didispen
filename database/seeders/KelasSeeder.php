<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $jurusanMap = Jurusan::pluck('id', 'kode_jurusan');

        $kelasData = [
            // PPLG
            ['nama_kelas' => 'X PPLG 1', 'tingkat' => 'X', 'kode_jurusan' => 'PPLG'],
            ['nama_kelas' => 'X PPLG 2', 'tingkat' => 'X', 'kode_jurusan' => 'PPLG'],
            ['nama_kelas' => 'XI PPLG 1', 'tingkat' => 'XI', 'kode_jurusan' => 'PPLG'],
            ['nama_kelas' => 'XII PPLG 1', 'tingkat' => 'XII', 'kode_jurusan' => 'PPLG'],

            // AKL
            ['nama_kelas' => 'X AKL 1', 'tingkat' => 'X', 'kode_jurusan' => 'AKL'],
            ['nama_kelas' => 'XI AKL 1', 'tingkat' => 'XI', 'kode_jurusan' => 'AKL'],
            ['nama_kelas' => 'XII AKL 1', 'tingkat' => 'XII', 'kode_jurusan' => 'AKL'],

            // MPLB
            ['nama_kelas' => 'X MPLB 1', 'tingkat' => 'X', 'kode_jurusan' => 'MPLB'],
            ['nama_kelas' => 'XI MPLB 1', 'tingkat' => 'XI', 'kode_jurusan' => 'MPLB'],
            ['nama_kelas' => 'XII MPLB 1', 'tingkat' => 'XII', 'kode_jurusan' => 'MPLB'],

            // PM
            ['nama_kelas' => 'X PM 1', 'tingkat' => 'X', 'kode_jurusan' => 'PM'],
            ['nama_kelas' => 'XI PM 1', 'tingkat' => 'XI', 'kode_jurusan' => 'PM'],
            ['nama_kelas' => 'XII PM 1', 'tingkat' => 'XII', 'kode_jurusan' => 'PM'],

            // TO
            ['nama_kelas' => 'X TO 1', 'tingkat' => 'X', 'kode_jurusan' => 'TO'],
            ['nama_kelas' => 'XI TO 1', 'tingkat' => 'XI', 'kode_jurusan' => 'TO'],
            ['nama_kelas' => 'XII TO 1', 'tingkat' => 'XII', 'kode_jurusan' => 'TO'],
        ];

        foreach ($kelasData as $item) {
            $jurusanId = $jurusanMap[$item['kode_jurusan']] ?? 1;

            Kelas::firstOrCreate(
                ['nama_kelas' => $item['nama_kelas']],
                [
                    'jurusan_id' => $jurusanId,
                    'tingkat'    => $item['tingkat'],
                ]
            );
        }

        $this->command->info('✅ KelasSeeder selesai. (16 Kelas manual berhasil dibuat)');
    }
}
