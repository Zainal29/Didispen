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
        // 1. Buat 1 Guru saja (Budi Santoso)
        $user = User::firstOrCreate(
            ['email' => 'budi@sch.id'],
            [
                'name'     => 'Budi Santoso, S.Pd.',
                'password' => Hash::make('password'),
                'role'     => 'guru',
                'nis_nip'  => '198701152010011001',
            ]
        );

        Guru::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nip'            => '198701152010011001',
                'nama_lengkap'   => 'Budi Santoso, S.Pd.',
                'mata_pelajaran' => 'Matematika',
                'tanggal_lahir'  => '1987-01-15',
                'status_aktif'   => true,
            ]
        );

        $this->command->info('✅ GuruSeeder selesai. (1 Akun Guru: Budi Santoso berhasil dibuat)');
    }
}
