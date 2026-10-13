<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;

class CleanupDispensasiHistory extends Command
{
    protected $signature = 'dispensasi:cleanup-history';

    protected $description = 'Pindahkan riwayat dispensasi (selesai) yang berumur lebih dari 30 hari ke arsip (soft delete)';

    public function handle()
    {
        // Pindahkan ke arsip dispensasi selesai yang lebih dari 30 hari
        $dispensasiLama = Dispensasi::withoutTrashed()
            ->where('status', 'selesai')
            ->where('created_at', '<', now()->subDays(30))
            ->get();

        $count = $dispensasiLama->count();

        if ($count === 0) {
            $this->info("Tidak ada riwayat dispensasi lama yang perlu diarsipkan.");
            return 0;
        }

        // Soft delete otomatis memindahkan ke arsip tanpa menghapus permanen
        foreach ($dispensasiLama as $dispensasi) {
            $dispensasi->delete();
        }

        $this->info("Berhasil mengarsipkan {$count} riwayat dispensasi lama.");

        return 0;
    }
}
