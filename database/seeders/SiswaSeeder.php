<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $fakerID = FakerFactory::create('id_ID');

        // 1. Buat akun khusus untuk Zainal (Siswa)
        $userZainal = User::firstOrCreate(
            ['email' => 'zainal@gmail.com'],
            [
                'name' => 'Zainal',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis_nip' => '20269999',
            ]
        );

        // Buat relasi ke tabel siswa jika belum ada
        Siswa::firstOrCreate(
            ['user_id' => $userZainal->id],
            [
                'kelas_id' => Kelas::inRandomOrder()->first()?->id ?? 1,
                'nama_lengkap' => 'Zainal',
                'tanggal_lahir' => '2008-01-01',
                'alamat' => $fakerID->address(),
                'no_telepon' => $fakerID->phoneNumber(),
            ]
        );

        // 2. Generate 50 Siswa dummy tambahan menggunakan Factory
        Siswa::factory()->count(50)->create();

        $this->command->info('✅ SiswaSeeder selesai. (Akun khusus zainal@gmail.com & 50 Siswa dinamis berhasil dibuat)');
    }
}
