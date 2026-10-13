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
            $dispensasiDate = $dispensasi->tanggal_izin
                ? Carbon::parse($dispensasi->tanggal_izin, 'Asia/Jakarta')
                : ($dispensasi->created_at ? $dispensasi->created_at->copy()->setTimezone('Asia/Jakarta') : $now);

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

                $notifMessage = null;

                // 1. KASUS MENUNGGU: Siswa tidak diproses / tidak ke guru piket hingga hari KBM berakhir
                if ($locked->status === 'menunggu') {
                    $locked->update([
                        'status' => 'kadaluarsa',
                        'catatan_admin' => 'Kadaluarsa otomatis: Pengajuan tidak diproses hingga jam KBM berakhir / hari berganti.',
                    ]);
                    \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($locked);
                    $count++;
                    return;
                }

                // 2. KASUS DISETUJUI TAPI TIDAK KELUAR: Siswa tidak pernah scan ke Satpam
                if ($locked->status === 'disetujui' && empty($locked->waktu_keluar_aktual)) {
                    $locked->update([
                        'status' => 'kadaluarsa',
                        'catatan_admin' => 'Kadaluarsa otomatis: Siswa tidak melakukan scan keluar di pos gerbang hingga KBM berakhir.',
                    ]);
                    \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($locked);
                    $count++;
                    return;
                }

                // 3. KASUS KELUAR:
                if ($locked->status === 'keluar') {
                    // 3a. Izin Sampai Pulang: Ditutup selesai tanpa peringatan terlambat
                    if ($locked->sampai_pulang) {
                        $locked->update([
                            'status' => 'selesai',
                            'catatan_admin' => 'Selesai otomatis: Siswa izin sampai pulang sekolah.',
                        ]);
                        \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($locked);
                        $count++;
                        return;
                    }

                    // 3b. Izin Reguler tapi tidak scan kembali: Ditandai kadaluarsa / tidak kembali
                    $locked->update([
                        'status' => 'kadaluarsa',
                        'is_warned' => true,
                        'warned_at' => $now,
                        'catatan_admin' => 'Kadaluarsa otomatis: Siswa tidak melakukan scan kembali hingga KBM berakhir (Tidak Kembali).',
                    ]);
                    \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($locked);
                    $count++;

                    if ($this->notifikasiService && $locked->siswa?->user_id) {
                        try {
                            $this->notifikasiService->send(
                                $locked->siswa->user_id,
                                "PERINGATAN: Dispensasi Anda ({$locked->nomor_surat}) telah ditutup otomatis. Anda tercatat TIDAK KEMBALI / TANPA SCAN MASUK ke sekolah.",
                                route('siswa.pengajuan.show', $locked->id)
                            );
                        } catch (\Throwable $e) {
                            Log::warning("Gagal mengirim notifikasi auto-complete tidak kembali: " . $e->getMessage());
                        }
                    }
                }
            });
        }

        $this->info("Berhasil auto-selesaikan {$count} dispensasi setelah jam KBM terakhir.");

        return 0;
    }
}
