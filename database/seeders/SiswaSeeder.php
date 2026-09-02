<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Jurusan & Kelas ada (Anti-Error jika tabel masih kosong)
        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'PPLG'],
            ['nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim']
        );

        $kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'XII PPLG 1'],
            ['jurusan_id' => $jurusan->id, 'tingkat' => 'XII']
        );

        // 2. Buat 1 Siswa saja (Zainal)
        $user = User::firstOrCreate(
            ['email' => 'zainal@gmail.com'],
            [
                'name'     => 'Zainal',
                'password' => Hash::make('password'),
                'role'     => 'siswa',
                'nis_nip'  => '20269999',
            ]
        );

        Siswa::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nis_nip'       => '20269999',
                'kelas_id'      => $kelas->id,
                'jurusan_id'    => $jurusan->id,
                'nama_lengkap'  => 'Zainal',
                'tanggal_lahir' => '2008-01-15',
                'alamat'        => 'Jl. Raya Bangsri No. 45, Jepara',
                'no_telepon'    => '+6281234567890',
                'status_aktif'  => true,
            ]
        );

        $this->command->info('✅ SiswaSeeder selesai. (1 Akun Siswa: Zainal Abidin berhasil dibuat)');
    }
}
