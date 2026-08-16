<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as FakerFactory; // <-- Tambahkan import ini

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        // Buat instance Faker khusus locale Indonesia (id_ID)
        $fakerID = FakerFactory::create('id_ID');

        $namaLengkap = $fakerID->name(); // Menghasilkan nama Indonesia
        $nip = $fakerID->unique()->numerify('198#########');

        // 1. Generate otomatis akun User untuk guru
        $user = User::create([
            'name' => $namaLengkap,
            'email' => $fakerID->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nis_nip' => $nip,
        ]);

        // 2. Relasikan dengan tabel Guru
        return [
            'user_id' => $user->id,
            'nip' => $nip,
            'nama_lengkap' => $namaLengkap,
            'mata_pelajaran' => $fakerID->randomElement(['Matematika', 'B. Indonesia', 'B. Inggris', 'Produktif', 'Penjasorkes', 'Sejarah']),
        ];
    }
}