<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruPiket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $emailPiket = 'gurupiket@smkn1bangsri.sch.id';
        $passwordPiket = 'gurupiket2026';

        // 1. Buat SATU akun shared untuk Guru Piket Utama
        $user = User::firstOrCreate(
            ['email' => $emailPiket],
            [
                'name' => 'Guru Piket Utama',
                'password' => Hash::make($passwordPiket),
                'role' => 'guru',
            ]
        );

        // 2. Buat data Guru untuk akun shared
        $guru = Guru::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nip' => 'PIKET001',
                'nama_lengkap' => 'GURU PIKET UTAMA',
                'mata_pelajaran' => '-',
            ]
        );

        // 3. Buat jadwal piket 7 hari otomatis untuk Guru Piket Utama
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
        foreach ($hariList as $hari) {
            GuruPiket::updateOrCreate(
                ['hari' => $hari], 
                ['guru_id' => $guru->id]
            );
        }

        // 4. TAMBAHAN: Generate 5 Guru dummy tambahan menggunakan GuruFactory
        Guru::factory()->count(5)->create();

        $this->command->info('✅ Akun Guru Piket (Shared) & 5 Guru Tambahan siap digunakan!');
        $this->command->info('📧 Email    : ' . $emailPiket);
        $this->command->info('🔑 Password : ' . $passwordPiket);
        $this->command->info('📅 Jadwal 7 hari otomatis dibuat.');
    }
}