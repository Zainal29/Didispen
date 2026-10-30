<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupFotoVerifikasi extends Command
{
    protected $signature = 'dispensasi:cleanup-foto {--orphan : Bersihkan juga file foto yatim di disk storage yang tidak terkait dispensasi aktif}';

    protected $description = 'Hapus file fisik foto verifikasi dan foto bukti untuk dispensasi yang sudah berstatus final (selesai, ditolak, dibatalkan, kadaluarsa)';

    public function handle()
    {
        // 1. Bersihkan foto dispensasi yang statusnya sudah final
        $finalStatuses = ['selesai', 'ditolak', 'dibatalkan', 'kadaluarsa'];

        $dispensasi = Dispensasi::whereIn('status', $finalStatuses)
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
                'foto_bukti'      => null,
            ]);
            $count++;
        }

        $this->info("Berhasil membersihkan foto untuk {$count} data dispensasi berstatus final.");

        // 2. Jika diminta atau flag --orphan aktif, periksa file yatim di direktori storage
        if ($this->option('orphan') || true) {
            $orphanCount = $this->cleanupOrphanFiles();
            if ($orphanCount > 0) {
                $this->info("Berhasil membersihkan {$orphanCount} file foto sisa (orphan files) di storage.");
            }
        }

        return 0;
    }

    /**
     * Bersihkan file foto di folder storage yang sudah tidak dipakai oleh dispensasi aktif
     */
    private function cleanupOrphanFiles(): int
    {
        $activePhotos = Dispensasi::whereIn('status', ['menunggu', 'disetujui', 'keluar'])
            ->pluck('foto_verifikasi')
            ->merge(
                Dispensasi::whereIn('status', ['menunggu', 'disetujui', 'keluar'])->pluck('foto_bukti')
            )
            ->filter()
            ->values()
            ->toArray();

        $directories = ['foto_verifikasi', 'foto-verifikasi', 'foto-bukti', 'bukti_dispensasi'];
        $removed = 0;

        foreach ($directories as $dir) {
            if (! Storage::disk('public')->exists($dir)) {
                continue;
            }

            $files = Storage::disk('public')->files($dir);
            foreach ($files as $file) {
                // Jangan hapus jika file dipakai dispensasi yang masih berjalan
                if (in_array($file, $activePhotos, true)) {
                    continue;
                }

                // Jangan hapus file .gitignore
                if (str_ends_with($file, '.gitignore')) {
                    continue;
                }

                // Hapus file yang tidak lagi terkait data aktif
                Storage::disk('public')->delete($file);
                $removed++;
            }
        }

        return $removed;
    }
}
