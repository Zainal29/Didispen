<?php

namespace Database\Factories;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

class DispensasiFactory extends Factory
{
    protected $model = Dispensasi::class;

    public function definition(): array
    {
        $siswa = Siswa::inRandomOrder()->first();
        $guru = Guru::inRandomOrder()->first();

        $jamKeluar = fake()->numberBetween(1, 7);

        return [
            'siswa_id' => $siswa ? $siswa->id : 1,
            'guru_id' => $guru ? $guru->id : null,
            'nomor_surat' => 'DISP-' . fake()->unique()->numerify('2026####'),
            'kategori' => fake()->randomElement(['sakit', 'izin', 'keperluan_sekolah', 'lainnya']),
            'alasan' => fake()->sentence(5),
            'tujuan' => fake()->randomElement(['Puskesmas', 'Rumah Sakit', 'Lomba Antar Sekolah', 'Urus Administrasi']),
            'lokasi' => fake()->city() . ', ' . fake()->streetName(),
            'jam_keluar' => 'Jam Pelajaran ke-' . $jamKeluar,
            'jam_kembali' => 'Jam Pelajaran ke-' . ($jamKeluar + 2),
            'status' => fake()->randomElement(['menunggu', 'disetujui', 'ditolak', 'keluar', 'selesai']),
            'catatan_admin' => fake()->optional(0.3)->sentence(3),
            'print_count' => fake()->numberBetween(0, 3),
            'max_print_limit' => 3,
        ];
    }
}