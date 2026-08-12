<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SatpamSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'satpam@smkn1bangsri.sch.id'], // Cek agar tidak duplikat
            [
                'name' => 'Satpam Sekolah',
                'password' => Hash::make('satpam123'),
                'role' => 'satpam',
                'nis_nip' => 'SATPAM001',
            ]
        );

        $this->command->info('✅ Akun Satpam siap digunakan!');
        $this->command->info('📧 Email    : satpam@smkn1bangsri.sch.id');
        $this->command->info('🔑 Password : satpam123');
    }
}