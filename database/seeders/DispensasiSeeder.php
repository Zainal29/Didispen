```php
<?php

namespace Database\Seeders;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DispensasiSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data yang benar-benar tersedia di database
        $siswa = Siswa::first();
        $guru  = Guru::first();

        if (! $siswa) {
            $this->command->error(
                '❌ Tidak ada data siswa. Pastikan SiswaSeeder dijalankan terlebih dahulu.'
            );

            return;
        }

        if (! $guru) {
            $this->command->error(
                '❌ Tidak ada data guru. Pastikan GuruSeeder dijalankan terlebih dahulu.'
            );

            return;
        }

        $data = [
            [
                'nomor_surat'         => 'DISP-2026-0001',
                'siswa_id'            => $siswa->id,
                'guru_id'             => $guru->id,
                'kategori'            => 'sakit',
                'alasan'              => 'Sakit gigi, perlu kontrol ke dokter spesialis',
                'tujuan'              => 'Klinik Gigi Sehat Bangsri',
                'lokasi'              => 'Jl. Kesehatan No. 12',
                'jam_keluar'          => 'Jam Pelajaran ke-2',
                'jam_kembali'         => 'Jam Pelajaran ke-4',
                'batas_waktu_kembali' => now()->addHours(2),
                'status'              => 'disetujui',
                'catatan_admin'        => 'Harap kembali tepat waktu',
                'print_count'         => 1,
                'max_print_limit'     => 3,
                'qr_token'            => Str::random(64),
            ],
            [
                'nomor_surat'         => 'DISP-2026-0002',
                'siswa_id'            => $siswa->id,
                'guru_id'             => $guru->id,
                'kategori'            => 'izin',
                'alasan'              => 'Urusan keluarga mendadak',
                'tujuan'              => 'Rumah orang tua',
                'lokasi'              => 'Jl. Raya Bangsri No. 8',
                'jam_keluar'          => 'Jam Pelajaran ke-5',
                'jam_kembali'         => 'Jam Pelajaran ke-7',
                'batas_waktu_kembali' => now()->addHours(3),
                'status'              => 'disetujui',
                'catatan_admin'        => 'Dipersilakan',
                'print_count'         => 0,
                'max_print_limit'     => 3,
                'qr_token'            => Str::random(64),
            ],
            [
                'nomor_surat'         => 'DISP-2026-0003',
                'siswa_id'            => $siswa->id,
                'guru_id'             => null,
                'kategori'            => 'keperluan_sekolah',
                'alasan'              => 'Mengambil buku referensi lomba kompetensi',
                'tujuan'              => 'Perpustakaan Daerah Jepara',
                'lokasi'              => 'Jl. Alun-Alun No. 3, Jepara',
                'jam_keluar'          => 'Jam Pelajaran ke-3',
                'jam_kembali'         => 'Jam Pelajaran ke-4',
                'batas_waktu_kembali' => now()->addHour(),
                'status'              => 'menunggu',
                'catatan_admin'       => null,
                'print_count'         => 0,
                'max_print_limit'     => 3,
                'qr_token'            => Str::random(64),
            ],
        ];

        foreach ($data as $item) {
            Dispensasi::updateOrCreate(
                ['nomor_surat' => $item['nomor_surat']],
                $item
            );
        }

        $this->command->info(
            "✅ DispensasiSeeder selesai. "
            . "Menggunakan Siswa ID {$siswa->id} dan Guru ID {$guru->id}."
        );
    }
}
```
