<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruPiket;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GuruPiketSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada data guru sebelum membuat jadwal
        if (Guru::count() == 0) {
            $this->command->error('Data Guru kosong. Jalankan GuruSeeder terlebih dahulu!');
            return;
        }

        // Cari guru utama/spesial (gurupiket@smkn1bangsri.sch.id)
        $guruUtama = Guru::whereHas('user', function ($query) {
            $query->where('email', 'gurupiket@smkn1bangsri.sch.id');
        })->first();

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);
            
            // Jika guru utama ada, jadikan dia salah satu petugas piket secara bergantian (misal di shift pagi)
            if ($guruUtama) {
                // Hari genap guru utama shift pagi, hari ganjil shift siang (atau di-random)
                $guruPagi = ($i % 2 == 0) ? $guruUtama : Guru::where('id', '!=', $guruUtama->id)->inRandomOrder()->first();
                $guruSiang = ($i % 2 != 0) ? $guruUtama : Guru::where('id', '!=', $guruPagi->id)->inRandomOrder()->first() ?? $guruPagi;
            } else {
                // Fallback jika guru utama belum ada
                $guruPagi = Guru::inRandomOrder()->first();
                $guruSiang = Guru::where('id', '!=', $guruPagi->id)->inRandomOrder()->first() ?? $guruPagi;
            }
            
            // Shift Pagi
            GuruPiket::updateOrCreate(
                ['tanggal' => $date, 'shift' => 'pagi'], 
                ['guru_id' => $guruPagi->id]             
            );

            // Shift Siang
            GuruPiket::updateOrCreate(
                ['tanggal' => $date, 'shift' => 'siang'],
                ['guru_id' => $guruSiang->id]
            );
        }
        
        $this->command->info('✅ GuruPiketSeeder selesai. Jadwal piket dinamis berhasil dibuat (Akun Guru Piket Utama ikut dijadwalkan).');
    }
}