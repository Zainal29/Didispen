<?php

namespace App\Console\Commands;

use App\Helpers\TimeHelper;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCompleteDispensasi extends Command
{
    protected $signature = 'dispensasi:auto-complete';
    protected $description = 'Otomatis menyelesaikan dispensasi menggantung setelah jam KBM terakhir pada hari tersebut';

    public function __construct(
        protected ?NotifikasiService $notifikasiService = null
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now('Asia/Jakarta');

        // Target status: menunggu, disetujui, keluar
        $dispensasis = Dispensasi::with('siswa')
            ->whereIn('status', ['menunggu', 'disetujui', 'keluar'])
            ->get();

        $count = 0;

        foreach ($dispensasis as $dispensasi) {
            $dispensasiDate = $dispensasi->created_at
                ? $dispensasi->created_at->copy()->setTimezone('Asia/Jakarta')
                : $now;

            // Periksa apakah waktu sekarang sudah melewati jam KBM terakhir pada tanggal dispensasi
            if (! TimeHelper::isKbmHariSelesai($now, $dispensasiDate)) {
                continue;
            }

            // Transaksi aman dengan lockForUpdate untuk mencegah race condition
            DB::transaction(function () use ($dispensasi, $now, &$count) {
                $locked = Dispensasi::whereKey($dispensasi->id)
                    ->whereIn('status', ['menunggu', 'disetujui', 'keluar'])
                    ->lockForUpdate()
                    ->first();

                if (! $locked) {
                    return;
                }

                $updateData = [
                    'status' => 'selesai',
                ];

                // Jika statusnya keluar dan batas waktu kembali terlewati, catat status keterlambatan
                $isLate = false;
                if ($locked->status === 'keluar' && $locked->batas_waktu_kembali) {
                    $batasKembali = $locked->batas_waktu_kembali->copy()->setTimezone('Asia/Jakarta');
                    if ($now->greaterThan($batasKembali)) {
                        $isLate = true;
                        $updateData['is_warned'] = true;
                        if (! $locked->warned_at) {
                            $updateData['warned_at'] = $now;
                        }
                    }
                }

                // Kontrak timestamp:
                // - waktu_kembali_aktual TIDAK BOLEH diisi
                // - waktu_keluar_aktual tetap ada jika sebelumnya keluar, null jika belum keluar
                // - approved_at tetap ada jika sebelumnya disetujui, null jika menunggu
                // - rejected_at tetap null
                // - foto_verifikasi TIDAK dihapus saat auto-complete
                $locked->update($updateData);
                $count++;

                // Notifikasi keterlambatan jika siswa keluar dan terlambat
                if ($isLate && $this->notifikasiService && $locked->siswa?->user_id) {
                    try {
                        $this->notifikasiService->send(
                            $locked->siswa->user_id,
                            "PERINGATAN: Dispensasi Anda ({$locked->nomor_surat}) telah diselesaikan otomatis karena jam KBM telah berakhir. Anda tercatat TERLAMBAT kembali.",
                            route('siswa.pengajuan.show', $locked->id)
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Gagal mengirim notifikasi auto-complete terlambat: " . $e->getMessage());
                    }
                }
            });
        }

        $this->info("Berhasil auto-selesaikan {$count} dispensasi setelah jam KBM terakhir.");

        return 0;
    }
}
