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
            ['email' => 'satpam@smkn1bangsri.sch.id'],
            [
                'name'                 => 'Petugas Satpam',
                'password'             => Hash::make('satpam123'),
                'role'                 => 'satpam',
                'nis_nip'              => 'SATPAM001',
                'must_change_password' => false,
            ]
        );

        $this->command->info('✅ SatpamSeeder selesai. (Akun Satpam siap digunakan: satpam@smkn1bangsri.sch.id / satpam123)');
    }
}
