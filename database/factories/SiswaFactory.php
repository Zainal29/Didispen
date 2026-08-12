<?php

namespace Database\Factories;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        $namaLengkap = $this->faker->name();

        // 1. Generate otomatis akun User untuk siswa ini
        $user = User::create([
            'name' => $namaLengkap,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // Default password
            'role' => 'siswa',
            'nis_nip' => $this->faker->unique()->numerify('2026####'), // NIS otomatis
        ]);

        // 2. Relasikan dengan tabel Siswa
        return [
            'user_id' => $user->id,
            'jurusan_id' => Jurusan::inRandomOrder()->first()->id ?? 1,
            'kelas_id' => Kelas::inRandomOrder()->first()->id ?? 1,
            'nama_lengkap' => $namaLengkap,
            'tanggal_lahir' => $this->faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'alamat' => $this->faker->address(),
            'no_telepon' => $this->faker->phoneNumber(),
        ];
    }
}