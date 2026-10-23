<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SatpamSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'satpam@smkn1bangsri.sch.id'],
            [
                'name'     => 'Petugas Satpam',
                'password' => Hash::make('satpam123'),
                'role'     => 'satpam',
                'nis_nip'  => 'SATPAM001',
                'no_wa'    => '+6289501963463',
            ]
        );

        $this->command->info('✅ SatpamSeeder selesai. (Akun Satpam siap digunakan: satpam@smkn1bangsri.sch.id / satpam123)');
    }
}
