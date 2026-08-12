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

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i);
            
            // Ambil 2 guru acak untuk shift pagi dan siang
            $guruPagi = Guru::inRandomOrder()->first();
            $guruSiang = Guru::where('id', '!=', $guruPagi->id)->inRandomOrder()->first() ?? $guruPagi;
            
            // Shift Pagi (Dinamis)
            GuruPiket::updateOrCreate(
                ['tanggal' => $date, 'shift' => 'pagi'], 
                ['guru_id' => $guruPagi->id]             
            );

            // Shift Siang (Dinamis)
            GuruPiket::updateOrCreate(
                ['tanggal' => $date, 'shift' => 'siang'],
                ['guru_id' => $guruSiang->id]
            );
        }
        
        $this->command->info('✅ GuruPiketSeeder selesai. Jadwal piket dinamis berhasil dibuat.');
    }
}