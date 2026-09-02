<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Jurusan;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        $faker = FakerFactory::create();

        // Ambil jurusan acak dari database
        $jurusan = Jurusan::inRandomOrder()->first();
        $kodeJurusan = $jurusan ? $jurusan->kode_jurusan : 'UMUM';

        $tingkat = $faker->randomElement(['X', 'XI', 'XII']);
        $nomorKelas = $faker->numberBetween(1, 4);

        return [
            'jurusan_id' => $jurusan ? $jurusan->id : 1,
            'nama_kelas' => "$tingkat $kodeJurusan $nomorKelas",
            'tingkat' => $tingkat,
        ];
    }
}
