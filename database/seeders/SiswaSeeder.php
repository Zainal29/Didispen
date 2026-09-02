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
        $jurusanMap = Jurusan::pluck('id', 'kode_jurusan');
        $kelasMap   = Kelas::pluck('id', 'nama_kelas');

        $siswas = [
            [
                'name'          => 'Zainal Abidin',
                'email'         => 'zainal@gmail.com',
                'nis'           => '20269999',
                'nama_kelas'    => 'XII PPLG 1',
                'kode_jurusan'  => 'PPLG',
                'tanggal_lahir' => '2008-01-15',
                'alamat'        => 'Jl. Raya Bangsri No. 45, Jepara',
                'no_telepon'    => '+6281234567890',
            ],
            [
                'name'          => 'Ahmad Fauzi',
                'email'         => 'ahmad@sch.id',
                'nis'           => '20260001',
                'nama_kelas'    => 'XII PPLG 1',
                'kode_jurusan'  => 'PPLG',
                'tanggal_lahir' => '2008-03-22',
                'alamat'        => 'Jl. Pemuda No. 12, Bangsri, Jepara',
                'no_telepon'    => '+6281234567891',
            ],
            [
                'name'          => 'Dewi Lestari',
                'email'         => 'dewi@sch.id',
                'nis'           => '20260002',
                'nama_kelas'    => 'XI AKL 1',
                'kode_jurusan'  => 'AKL',
                'tanggal_lahir' => '2008-07-10',
                'alamat'        => 'Jl. Kartini No. 8, Bangsri, Jepara',
                'no_telepon'    => '+6281234567892',
            ],
            [
                'name'          => 'Rizky Pratama',
                'email'         => 'rizky@sch.id',
                'nis'           => '20260003',
                'nama_kelas'    => 'XI MPLB 1',
                'kode_jurusan'  => 'MPLB',
                'tanggal_lahir' => '2008-09-05',
                'alamat'        => 'Jl. Diponegoro No. 17, Jepara',
                'no_telepon'    => '+6281234567893',
            ],
            [
                'name'          => 'Siti Aisyah',
                'email'         => 'aisyah@sch.id',
                'nis'           => '20260004',
                'nama_kelas'    => 'X PPLG 1',
                'kode_jurusan'  => 'PPLG',
                'tanggal_lahir' => '2009-02-14',
                'alamat'        => 'Jl. Veteran No. 3, Bangsri, Jepara',
                'no_telepon'    => '+6281234567894',
            ],
            [
                'name'          => 'Bagas Pratama',
                'email'         => 'bagas@sch.id',
                'nis'           => '20260005',
                'nama_kelas'    => 'X TO 1',
                'kode_jurusan'  => 'TO',
                'tanggal_lahir' => '2009-05-30',
                'alamat'        => 'Jl. Industri No. 20, Mlonggo, Jepara',
                'no_telepon'    => '+6281234567895',
            ],
            [
                'name'          => 'Putri Anggraini',
                'email'         => 'putri@sch.id',
                'nis'           => '20260006',
                'nama_kelas'    => 'XII PM 1',
                'kode_jurusan'  => 'PM',
                'tanggal_lahir' => '2007-11-28',
                'alamat'        => 'Jl. Pahlawan No. 5, Jepara',
                'no_telepon'    => '+6281234567896',
            ],
            [
                'name'          => 'Dimas Saputra',
                'email'         => 'dimas@sch.id',
                'nis'           => '20260007',
                'nama_kelas'    => 'X AKL 1',
                'kode_jurusan'  => 'AKL',
                'tanggal_lahir' => '2009-08-19',
                'alamat'        => 'Jl. Ahmad Yani No. 99, Bangsri, Jepara',
                'no_telepon'    => '+6281234567897',
            ],
        ];

        foreach ($siswas as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name'     => $item['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'siswa',
                    'nis_nip'  => $item['nis'],
                ]
            );

            $kelasId   = $kelasMap[$item['nama_kelas']] ?? Kelas::first()->id;
            $jurusanId = $jurusanMap[$item['kode_jurusan']] ?? Jurusan::first()->id;

            Siswa::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nis_nip'       => $item['nis'],
                    'kelas_id'      => $kelasId,
                    'jurusan_id'    => $jurusanId,
                    'nama_lengkap'  => $item['name'],
                    'tanggal_lahir' => $item['tanggal_lahir'],
                    'alamat'        => $item['alamat'],
                    'no_telepon'    => $item['no_telepon'],
                    'status_aktif'  => true,
                ]
            );
        }

        $this->command->info('✅ SiswaSeeder selesai. (8 Akun Siswa manual berhasil dibuat)');
    }
}
