<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Utama Sistem
        $admin = User::firstOrCreate(
            ['email' => 'admin@sch.id'],
            [
                'name'          => 'Administrator Sistem',
                'password'      => Hash::make('password'),
                'role'          => 'admin',
                'is_superadmin' => true,
                'nis_nip'       => 'ADMIN001',
            ]
        );
        if (!$admin->is_superadmin) {
            $admin->update(['is_superadmin' => true]);
        }

        $this->command->info('✅ UserSeeder selesai. (Akun Admin Utama siap digunakan: admin@sch.id / password)');
    }
}
