<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;

class CleanupDispensasiHistory extends Command
{
    protected $signature = 'dispensasi:cleanup-history';

    protected $description = 'Hapus riwayat dispensasi yang sudah dihubungi lebih dari 24 jam';

    public function handle()
    {
        $deleted = Dispensasi::where('is_warned', true)
            ->where('warned_at', '<', now()->subHours(24))
            ->where('status', 'selesai')
            ->delete();

        $this->info("✅ Berhasil menghapus {$deleted} riwayat dispensasi lama.");

        return 0;
    }
}
