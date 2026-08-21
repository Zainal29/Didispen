<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruPiket;
use Illuminate\Database\Seeder;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $jumlahGuru = 10;

        // 1. Buat akun guru secara individual menggunakan Factory
        Guru::factory()->count($jumlahGuru)->create();

        // 2. PENTING: Isi tabel guru_piket minimal 1 guru untuk setiap hari (Placeholder)
        // Ini WAJIB ada agar DispensasiSeeder tidak error (Foreign Key Constraint)
        // dan agar fitur validasi "guru piket hari ini" bisa dites.
        $guruPertama = Guru::first();
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        foreach ($hariList as $hari) {
            GuruPiket::updateOrCreate(
                ['hari' => $hari],
                ['guru_id' => $guruPertama ? $guruPertama->id : 1]
            );
        }

        $this->command->info("✅ GuruSeeder selesai. {$jumlahGuru} Akun Guru & Jadwal Piket Dasar (Placeholder) berhasil dibuat.");
        $this->command->info('ℹ️  Catatan: Jadwal piket di atas hanya placeholder agar database tidak error. Admin dapat mengubahnya nanti di menu Jadwal Piket sesuai jadwal WA.');
    }
}
