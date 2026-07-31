<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->insert([
            ['name' => 'Admin Sekolah', 'email' => 'admin@sch.id', 'password' => Hash::make('password'), 'role' => 'admin', 'nis_nip' => 'ADMIN001', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Budi Santoso', 'email' => 'budi@sch.id', 'password' => Hash::make('password'), 'role' => 'guru', 'nis_nip' => '1987654321', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siti Rahayu', 'email' => 'siti@sch.id', 'password' => Hash::make('password'), 'role' => 'guru', 'nis_nip' => '1987654322', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@sch.id', 'password' => Hash::make('password'), 'role' => 'siswa', 'nis_nip' => '2025001', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@sch.id', 'password' => Hash::make('password'), 'role' => 'siswa', 'nis_nip' => '2025002', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rizky Pratama', 'email' => 'rizky@sch.id', 'password' => Hash::make('password'), 'role' => 'siswa', 'nis_nip' => '2025003', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
