<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupFotoVerifikasi extends Command
{
    protected $signature = 'dispensasi:cleanup-foto';

    protected $description = 'Hapus foto verifikasi untuk dispensasi yang sudah selesai';

    public function handle()
    {
        // Cari dispensasi yang sudah selesai tapi masih punya foto
        $dispensasi = Dispensasi::where('status', 'selesai')
            ->whereNotNull('foto_verifikasi')
            ->get();

        $count = 0;
        foreach ($dispensasi as $d) {
            if ($d->foto_verifikasi && Storage::disk('public')->exists($d->foto_verifikasi)) {
                Storage::disk('public')->delete($d->foto_verifikasi);
            }

            $d->update(['foto_verifikasi' => null]);
            $count++;
        }

        $this->info("✅ Berhasil menghapus {$count} foto verifikasi lama.");

        return 0;
    }
}
