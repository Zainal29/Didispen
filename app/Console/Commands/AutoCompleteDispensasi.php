<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use App\Services\NotifikasiService; // ✅ TAMBAHKAN INI
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AutoCompleteDispensasi extends Command
{
    protected $signature = 'dispensasi:auto-complete';
    protected $description = 'Otomatis menyelesaikan dispensasi yang sudah melewati batas waktu kembali';

    public function __construct(
           protected NotifikasiService $notifikasiService // ✅ INJEKSI SERVICE
       ) {
           parent::__construct();
       }

    public function handle()
    {
        $now = now();

        $dispensasis = Dispensasi::where('status', 'keluar')
            ->whereNotNull('batas_waktu_kembali')
            ->where('batas_waktu_kembali', '<', $now)
            ->get();

        foreach ($dispensasis as $dispensasi) {
            $dispensasi->update([
                'status' => 'selesai',
                // ✅ JUJUR: Isi dengan waktu cron berjalan, bukan waktu batas
                'waktu_kembali_aktual' => $now,
                'is_warned' => true,
                'warned_at' => $now,
            ]);

            // ✅ HAPUS BLOK INI (Jangan hapus foto di sini, biarkan cleanup command)
            // if ($dispensasi->foto_verifikasi) { ... }

            // ✅ TAMBAHKAN INI: Kirim Notifikasi Terlambat
                       $this->notifikasiService->send(
                           $dispensasi->siswa->user_id,
                           "PERINGATAN: Anda TERLAMBAT kembali dari dispensasi ({$dispensasi->nomor_surat}). Segera lapor ke Pos Satpam.",
                           route('siswa.pengajuan.show', $dispensasi->id)
                       );
        }

        $this->info("Berhasil auto-selesaikan {$dispensasis->count()} dispensasi yang terlambat.");
    }
}
