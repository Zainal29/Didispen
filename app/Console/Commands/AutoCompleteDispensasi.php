<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCompleteDispensasi extends Command
{
    protected $signature = 'dispensasi:auto-complete';

    protected $description = 'Otomatis menyelesaikan dispensasi yang melewati jam pulang sekolah (15:00)';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');

        // ✅ REVISI: Hanya jalankan setelah jam 15:00
        if ($currentTime < '15:00') {
            $this->info("ℹ️ Belum jam pulang sekolah. Saat ini: {$currentTime}");

            return;
        }

        // Cari dispensasi yang:
        // 1. Status masih 'keluar'
        // 2. Tanggal hari ini
        // 3. Jam kembali adalah jam ke-9 atau ke-10 (jam pulang)
        $dispensasiList = Dispensasi::where('status', 'keluar')
            ->whereDate('created_at', today())
            ->where(function ($query) {
                $query->where('jam_kembali', 'like', '%ke-9%')
                    ->orWhere('jam_kembali', 'like', '%ke-10%');
            })
            ->get();

        $count = 0;
        foreach ($dispensasiList as $dispensasi) {
            $dispensasi->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => null, // Null karena otomatis, bukan scan satpam
                'catatan_admin' => 'Otomatis selesai (melewati jam pulang sekolah)',
            ]);
            $count++;
        }

        // ✅ REVISI: Output lebih rapi
        if ($count > 0) {
            $this->info("✅ Berhasil menyelesaikan {$count} dispensasi secara otomatis.");
        } else {
            $this->info('ℹ️ Tidak ada dispensasi yang perlu diselesaikan otomatis saat ini.');
        }
    }
}
