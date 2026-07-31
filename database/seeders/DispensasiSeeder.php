<?php

namespace Database\Seeders;

use App\Models\Dispensasi;
use Illuminate\Database\Seeder;

class DispensasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nomor_surat' => 'DISP-2026-0001',
                'siswa_id' => 1,
                'guru_piket_id' => 1,
                'kategori' => 'sakit', // ✅ Diubah dari 'kesehatan'
                'alasan' => 'Sakit gigi, perlu kontrol ke dokter',
                'tujuan' => 'Klinik gigi dekat sekolah',
                'lokasi' => 'Jl. Kesehatan No. 12',
                'jam_keluar' => 'Jam Pelajaran ke-2', // ✅ Diubah menjadi string
                'jam_kembali' => 'Jam Pelajaran ke-4', // ✅ Diubah menjadi string
                'status' => 'ditolak',
                'catatan_admin' => 'Harap membawa surat dari orang tua',
                'print_count' => 0,
                'max_print_limit' => 3,
            ],
            [
                'nomor_surat' => 'DISP-2026-0002',
                'siswa_id' => 2,
                'guru_piket_id' => 1,
                'kategori' => 'izin', // ✅ Diubah dari 'pribadi'
                'alasan' => 'Ada urusan keluarga mendadak',
                'tujuan' => 'Rumah orang tua',
                'lokasi' => 'Jl. Keluarga No. 8',
                'jam_keluar' => 'Jam Pelajaran ke-5', // ✅ Diubah menjadi string
                'jam_kembali' => 'Jam Pelajaran ke-7', // ✅ Diubah menjadi string
                'status' => 'disetujui',
                'catatan_admin' => 'Dipersilakan',
                'print_count' => 0,
                'max_print_limit' => 3,
            ],
            [
                'nomor_surat' => 'DISP-2026-0003',
                'siswa_id' => 3,
                'guru_piket_id' => 1,
                'kategori' => 'keperluan_sekolah', // ✅ Diubah dari 'lainnya' (opsional)
                'alasan' => 'Izin ke luar untuk mengambil buku referensi',
                'tujuan' => 'Perpustakaan umum',
                'lokasi' => 'Jl. Baca No. 3',
                'jam_keluar' => 'Jam Pelajaran ke-3', // ✅ Diubah menjadi string
                'jam_kembali' => 'Jam Pelajaran ke-4', // ✅ Diubah menjadi string
                'status' => 'menunggu',
                'catatan_admin' => null,
                'print_count' => 0,
                'max_print_limit' => 3,
            ],
        ];

        foreach ($data as $item) {
            Dispensasi::create($item);
        }
    }
}