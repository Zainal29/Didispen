<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;

class CleanupDispensasiHistory extends Command
{
    protected $signature = 'dispensasi:cleanup-history';

    protected $description = 'Hapus riwayat dispensasi (selesai) yang berumur lebih dari 30 hari untuk menghemat storage';

    public function handle()
    {
        // ✅ PERBAIKAN 1: Hapus berdasarkan umur data (30 hari), bukan hanya yang di-warned
        // ✅ PERBAIKAN 2: Pastikan hanya hapus yang statusnya sudah 'selesai'
        $dispensasiLama = Dispensasi::where('status', 'selesai')
            ->where('created_at', '<', now()->subDays(30)) // Simpan data selama 30 hari
            ->get();

        $count = $dispensasiLama->count();

        if ($count === 0) {
            $this->info("Tidak ada riwayat dispensasi lama yang perlu dihapus.");
            return 0;
        }

        // Looping untuk memastikan hook 'deleted' di Model terpanggil (File ikut terhapus)
        foreach ($dispensasiLama as $dispensasi) {
            $dispensasi->delete();
        }

        $this->info("Berhasil menghapus {$count} riwayat dispensasi lama beserta file fotonya.");

        return 0;
    }
}
