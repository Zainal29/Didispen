<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        $namaLengkap = $this->faker->name();
        $nip = $this->faker->unique()->numerify('198#########');

        // 1. Generate otomatis akun User untuk guru
        $user = User::create([
            'name' => $namaLengkap,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nis_nip' => $nip,
        ]);

        // 2. Relasikan dengan tabel Guru
        return [
            'user_id' => $user->id,
            'nip' => $nip,
            'nama_lengkap' => $namaLengkap,
            'mata_pelajaran' => $this->faker->randomElement(['Matematika', 'B. Indonesia', 'B. Inggris', 'Produktif', 'Penjasorkes', 'Sejarah']),
        ];
    }
}