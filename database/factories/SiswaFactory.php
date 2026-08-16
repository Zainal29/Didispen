<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        // Gunakan instance Faker lokal Indonesia (id_ID) untuk nama realistis
        $fakerID = FakerFactory::create('id_ID');

        $namaLengkap = $fakerID->name();
        $nomorInduk = $fakerID->unique()->numerify('2026####'); // Nomor induk siswa

        // 1. Buat otomatis akun User untuk siswa ini (menyimpan nis_nip sesuai struktur tabel users)
        $user = User::create([
            'name' => $namaLengkap,
            'email' => $fakerID->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis_nip' => $nomorInduk,
        ]);

        // 2. Relasikan dengan tabel Siswa (berdasarkan struktur kolom foreign key di phpMyAdmin)
        return [
            'user_id' => $user->id,
            'kelas_id' => Kelas::inRandomOrder()->first()?->id ?? 1,
            'nama_lengkap' => $namaLengkap,
            'tanggal_lahir' => $fakerID->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d'),
            'alamat' => $fakerID->address(),
            'no_telepon' => $fakerID->phoneNumber(),
        ];
    }
}
