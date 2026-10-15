<?php

namespace Database\Seeders;

use App\Models\Organisasi;
use Illuminate\Database\Seeder;

class OrganisasiSeeder extends Seeder
{
    public function run(): void
    {
        $organisasiList = [
            [
                'nama_organisasi' => 'OSIS',
                'singkatan' => 'OSIS',
                'kategori' => 'osis',
                'deskripsi' => 'Organisasi Siswa Intra Sekolah SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'Pramuka (Gudep 05.087 - 05.088)',
                'singkatan' => 'PRAMUKA',
                'kategori' => 'ekstrakurikuler',
                'deskripsi' => 'Gerakan Pramuka Pangkalan SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'Palang Merah Remaja (PMR Wira)',
                'singkatan' => 'PMR',
                'kategori' => 'ekstrakurikuler',
                'deskripsi' => 'Palang Merah Remaja Unit SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'Pasukan Pengibar Bendera (Paskibra)',
                'singkatan' => 'PASKIBRA',
                'kategori' => 'ekstrakurikuler',
                'deskripsi' => 'Korps Paskibra SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'Rohani Islam (Rohis)',
                'singkatan' => 'ROHIS',
                'kategori' => 'komunitas',
                'deskripsi' => 'Sie Kerohanian Islam SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'Klub Olahraga & Futsal',
                'singkatan' => 'FUTSAL',
                'kategori' => 'ekstrakurikuler',
                'deskripsi' => 'Ekstrakurikuler Olahraga Futsal SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'English Club',
                'singkatan' => 'EC',
                'kategori' => 'ekstrakurikuler',
                'deskripsi' => 'Komunitas Bahasa Inggris SMKN 1 Bangsri',
            ],
            [
                'nama_organisasi' => 'Seni Tari & Teater',
                'singkatan' => 'SENI',
                'kategori' => 'ekstrakurikuler',
                'deskripsi' => 'Komunitas Seni & Teater SMKN 1 Bangsri',
            ],
        ];

        foreach ($organisasiList as $data) {
            Organisasi::firstOrCreate(
                ['nama_organisasi' => $data['nama_organisasi']],
                $data
            );
        }

        $this->command->info('✅ OrganisasiSeeder selesai: data organisasi/ekstrakurikuler awal siap digunakan.');
    }
}
