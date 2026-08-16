<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruPiket;
use Illuminate\Database\Seeder;

class JadwalPiketSeeder extends Seeder
{
    public function run(): void
    {
        // Cari guru berdasarkan email user terkait (gurupiket@smkn1bangsri.sch.id)
        $defaultGuru = Guru::whereHas('user', function ($query) {
            $query->where('email', 'gurupiket@smkn1bangsri.sch.id');
        })->first();
        
        // Jika tidak ketemu berdasarkan email, fallback ambil guru pertama agar seeder tidak error
        if (!$defaultGuru) {
            $defaultGuru = Guru::first();
        }

        if (!$defaultGuru) {
            $this->command->error('Tidak ada data guru. Jalankan GuruSeeder terlebih dahulu!');
            return;
        }

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        
        foreach ($hariList as $hari) {
            GuruPiket::updateOrCreate(
                ['hari' => $hari],
                [
                    'guru_id' => $defaultGuru->id,
                ]
            );
        }

        $this->command->info('✅ Jadwal piket Senin-Jumat berhasil dibuat menggunakan akun guru piket default!');
    }
}