<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SatpamSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Petugas Satpam',
            'email' => 'satpam@smk.sch.id',
            'password' => bcrypt('satpam123'),
            'role' => 'satpam',
        ]);
    }
}