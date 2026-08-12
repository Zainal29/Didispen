<?php

namespace Database\Factories;

use App\Models\Dispensasi;
use App\Models\GuruPiket;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

class DispensasiFactory extends Factory
{
    protected $model = Dispensasi::class;

    public function definition(): array
    {
        // Ambil ID siswa dan guru piket secara acak dari database yang sudah ada
        // Pastikan Seeder ini dijalankan SETELAH SiswaSeeder dan GuruSeeder
        $siswa = Siswa::inRandomOrder()->first();
        $guruPiket = GuruPiket::inRandomOrder()->first();

        $jamKeluar = fake()->numberBetween(1, 7); // Jam pelajaran 1 sampai 7
        
        return [
            'siswa_id' => $siswa ? $siswa->id : 1, // Fallback ke 1 jika kosong
            'guru_piket_id' => $guruPiket ? $guruPiket->id : 1, // Fallback ke 1 jika kosong
            'nomor_surat' => 'DISP-' . fake()->unique()->numerify('2026####'),
            'kategori' => fake()->randomElement(['sakit', 'izin', 'keperluan_sekolah', 'lainnya']),
            'alasan' => fake()->sentence(5), // Kalimat acak 5 kata (misal: "Sakit gigi perlu kontrol ke dokter")
            'tujuan' => fake()->randomElement(['Puskesmas', 'Rumah Sakit', 'Lomba Antar Sekolah', 'Urus Administrasi']),
            'lokasi' => fake()->city() . ', ' . fake()->streetName(),
            'jam_keluar' => 'Jam Pelajaran ke-' . $jamKeluar,
            'jam_kembali' => 'Jam Pelajaran ke-' . ($jamKeluar + 2), // Selalu 2 jam setelah keluar
            'status' => fake()->randomElement(['menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai']),
            'catatan_admin' => fake()->optional(0.3)->sentence(3), // 30% kemungkinan ada catatan
            'print_count' => fake()->numberBetween(0, 3),
            'max_print_limit' => 3,
        ];
    }
}