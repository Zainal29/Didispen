<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Hardcode 1 Akun Guru Utama (Untuk testing login)
        $user = User::firstOrCreate(
            ['email' => 'gurupiket@smkn1bangsri.sch.id'],
            [
                'name' => 'Guru Piket Utama',
                'password' => Hash::make('gurupiket2026'),
                'role' => 'guru',
                'nis_nip' => 'PIKET001',
            ]
        );

        Guru::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nip' => 'PIKET001',
                'nama_lengkap' => 'Guru Piket Utama',
                'mata_pelajaran' => '-',
            ]
        );

        // 2. Generate 15 Guru Dinamis tambahan via Factory
        Guru::factory()->count(15)->create();

        $this->command->info('✅ GuruSeeder selesai. (1 Guru Statis + 15 Guru Dinamis dibuat)');
    }
}