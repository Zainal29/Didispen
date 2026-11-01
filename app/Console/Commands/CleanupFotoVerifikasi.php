<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupFotoVerifikasi extends Command
{
    protected $signature = 'dispensasi:cleanup-foto {--orphan : Bersihkan juga file foto yatim di disk storage yang tidak terkait dispensasi apa pun}';

    protected $description = 'Hapus file fisik foto verifikasi dan foto bukti untuk dispensasi yang sudah berstatus final (selesai, ditolak, dibatalkan, kadaluarsa)';

    public function handle()
    {
        // 1. Bersihkan foto dispensasi yang statusnya sudah final
        $finalStatuses = ['selesai', 'ditolak', 'dibatalkan', 'kadaluarsa'];

        $count = 0;

        // Gunakan withTrashed() agar data final yang sempat di-soft-delete juga dibersihkan fotonya
        // Gunakan chunkById untuk efisiensi memori (skalabilitas aman untuk ribuan data)
        Dispensasi::withTrashed()
            ->whereIn('status', $finalStatuses)
            ->where(function ($query) {
                $query->whereNotNull('foto_verifikasi')
                      ->orWhereNotNull('foto_bukti');
            })
            ->chunkById(100, function ($dispensasiList) use (&$count) {
                foreach ($dispensasiList as $d) {
                    if ($d->foto_verifikasi && Storage::disk('public')->exists($d->foto_verifikasi)) {
                        Storage::disk('public')->delete($d->foto_verifikasi);
                    }

                    if ($d->foto_bukti && Storage::disk('public')->exists($d->foto_bukti)) {
                        Storage::disk('public')->delete($d->foto_bukti);
                    }

                    // updateQuietly agar tidak memicu perubahan timestamp updated_at atau event listener berulang
                    $d->updateQuietly([
                        'foto_verifikasi' => null,
                        'foto_bukti'      => null,
                    ]);
                    $count++;
                }
            });

        $this->info("Berhasil membersihkan foto untuk {$count} data dispensasi berstatus final.");

        // 2. Jika diminta melalui flag --orphan, periksa file yatim di direktori storage
        if ($this->option('orphan')) {
            $orphanCount = $this->cleanupOrphanFiles();
            if ($orphanCount > 0) {
                $this->info("Berhasil membersihkan {$orphanCount} file foto sisa (orphan files) di storage.");
            } else {
                $this->info('Tidak ditemukan file foto sisa (orphan files) di storage.');
            }
        }

        return 0;
    }

    /**
     * Bersihkan file foto di folder storage yang tidak terhubung dengan dispensasi apa pun di database
     */
    private function cleanupOrphanFiles(): int
    {
        // Ambil semua path foto yang masih tercatat di database (termasuk yang di-soft-delete)
        // agar foto dari dispensasi yang ada di kotak sampah tidak ikut terhapus
        $referencedPhotos = Dispensasi::withTrashed()
            ->where(function ($query) {
                $query->whereNotNull('foto_verifikasi')
                      ->orWhereNotNull('foto_bukti');
            })
            ->get(['foto_verifikasi', 'foto_bukti'])
            ->flatMap(fn ($d) => [$d->foto_verifikasi, $d->foto_bukti])
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $directories = ['foto_verifikasi', 'foto-verifikasi', 'foto-bukti', 'foto_bukti', 'bukti_dispensasi'];
        $removed = 0;

        foreach ($directories as $dir) {
            if (! Storage::disk('public')->exists($dir)) {
                continue;
            }

            $files = Storage::disk('public')->files($dir);
            foreach ($files as $file) {
                // Jangan hapus jika file masih tercatat di data database (aktif maupun soft delete)
                if (in_array($file, $referencedPhotos, true)) {
                    continue;
                }

                // Jangan hapus file .gitignore
                if (str_ends_with($file, '.gitignore')) {
                    continue;
                }

                // Hapus file fisik yang benar-benar tidak bertuan (orphan)
                Storage::disk('public')->delete($file);
                $removed++;
            }
        }

        return $removed;
    }
}
