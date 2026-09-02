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
        $gurus = [
            [
                'name'           => 'Budi Santoso, S.Pd.',
                'email'          => 'budi@sch.id',
                'nip'            => '198701152010011001',
                'mata_pelajaran' => 'Matematika',
                'tanggal_lahir'  => '1987-01-15',
            ],
            [
                'name'           => 'Siti Rahayu, M.Pd.',
                'email'          => 'siti@sch.id',
                'nip'            => '198904202012022002',
                'mata_pelajaran' => 'Bahasa Indonesia',
                'tanggal_lahir'  => '1989-04-20',
            ],
            [
                'name'           => 'Eko Prasetyo, S.Kom.',
                'email'          => 'eko@sch.id',
                'nip'            => '198506122008011003',
                'mata_pelajaran' => 'Produktif PPLG',
                'tanggal_lahir'  => '1985-06-12',
            ],
            [
                'name'           => 'Sri Wahyuni, S.Pd.',
                'email'          => 'sri@sch.id',
                'nip'            => '199103082015032004',
                'mata_pelajaran' => 'Bahasa Inggris',
                'tanggal_lahir'  => '1991-03-08',
            ],
            [
                'name'           => 'Hendra Wijaya, S.Ag.',
                'email'          => 'hendra@sch.id',
                'nip'            => '198307252006041005',
                'mata_pelajaran' => 'Pendidikan Agama Islam',
                'tanggal_lahir'  => '1983-07-25',
            ],
            [
                'name'           => 'Rina Marlina, S.E.',
                'email'          => 'rina@sch.id',
                'nip'            => '199311182019022006',
                'mata_pelajaran' => 'Dasar Akuntansi',
                'tanggal_lahir'  => '1993-11-18',
            ],
            [
                'name'           => 'Agus Setiawan, S.T.',
                'email'          => 'agus@sch.id',
                'nip'            => '198002142005011007',
                'mata_pelajaran' => 'Teknik Kendaraan Ringan',
                'tanggal_lahir'  => '1980-02-14',
            ],
        ];

        foreach ($gurus as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'                 => $data['name'],
                    'password'             => Hash::make('password'),
                    'role'                 => 'guru',
                    'nis_nip'              => $data['nip'],
                    'must_change_password' => false,
                ]
            );

            Guru::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'            => $data['nip'],
                    'nama_lengkap'   => $data['name'],
                    'mata_pelajaran' => $data['mata_pelajaran'],
                    'tanggal_lahir'  => $data['tanggal_lahir'],
                    'status_aktif'   => true,
                ]
            );
        }

        $this->command->info('✅ GuruSeeder selesai. (7 Akun Guru manual berhasil disiapkan)');
    }
}
