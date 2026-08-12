<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruPiket;
use Illuminate\Database\Seeder;

class JadwalPiketSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil guru pertama yang ada (atau guru piket shared account)
        $defaultGuru = Guru::first();
        
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

        $this->command->info('✅ Jadwal piket Senin-Jumat berhasil dibuat!');
    }
}