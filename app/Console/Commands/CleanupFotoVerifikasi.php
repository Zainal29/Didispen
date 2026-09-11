<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupFotoVerifikasi extends Command
{
    protected $signature = 'dispensasi:cleanup-foto';

    protected $description = 'Hapus foto verifikasi dan foto bukti untuk dispensasi yang sudah selesai';

    public function handle()
    {
        // Cari dispensasi yang sudah selesai tapi masih punya foto verifikasi atau foto bukti
        $dispensasi = Dispensasi::where('status', 'selesai')
            ->where(function ($query) {
                $query->whereNotNull('foto_verifikasi')
                      ->orWhereNotNull('foto_bukti');
            })
            ->get();

        $count = 0;
        foreach ($dispensasi as $d) {
            if ($d->foto_verifikasi && Storage::disk('public')->exists($d->foto_verifikasi)) {
                Storage::disk('public')->delete($d->foto_verifikasi);
            }

            if ($d->foto_bukti && Storage::disk('public')->exists($d->foto_bukti)) {
                Storage::disk('public')->delete($d->foto_bukti);
            }

            $d->update([
                'foto_verifikasi' => null,
                'foto_bukti' => null,
            ]);
            $count++;
        }

        $this->info("Berhasil membersihkan foto verifikasi dan foto bukti untuk {$count} dispensasi yang sudah selesai.");

        return 0;
    }
}
