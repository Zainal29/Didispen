<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin@sch.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nis_nip' => 'ADMIN001',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@sch.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nis_nip' => '1987654321',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@sch.id',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'nis_nip' => '1987654322',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis_nip' => '2025001',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis_nip' => '2025002',
            ],
            [
                'name' => 'Rizky Pratama',
                'email' => 'rizky@sch.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'nis_nip' => '2025003',
            ],
        ];

        // ✅ Gunakan firstOrCreate agar tidak error jika data sudah ada
        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']], // Cek berdasarkan email (kolom unique)
                $userData                        // Buat data baru jika email belum ada
            );
        }

        $this->command->info('✅ UserSeeder selesai. Data user aman dari duplikasi.');
    }
}