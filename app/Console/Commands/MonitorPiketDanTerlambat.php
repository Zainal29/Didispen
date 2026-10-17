<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use App\Services\GuruPiketService;
use App\Services\WhatsappMessageService;
use Illuminate\Console\Command;

class MonitorPiketDanTerlambat extends Command
{
    protected $signature = 'didispen:monitor-wa';
    protected $description = 'Kirim notifikasi WA otomatis ke Guru Piket yang sedang bertugas & Siswa yang terlambat kembali';

    public function handle(GuruPiketService $piketService, WhatsappMessageService $waService): int
    {
        // 1. CEK GURU PIKET & KOORDINATOR YANG BERTUGAS SAAT INI
        $infoSesi = $piketService->getInformasiSesi();

        if ($infoSesi['status_sesi'] === 'Berlangsung' && ! $infoSesi['conflict'] && $infoSesi['jadwal']) {
            $jadwal = $infoSesi['jadwal'];

            // A. Notifikasi ke Guru Piket yang sedang bertugas
            foreach ($infoSesi['petugas'] as $petugas) {
                if ($petugas['status'] === 'Sedang Bertugas') {
                    // Akan otomatis di-skip jika hari ini di sesi ini sudah dikirimi WA (via reference_key)
                    $waService->notifyGuruPiketBertugas(
                        $petugas['guru'],
                        $jadwal,
                        $petugas['is_pengganti']
                    );
                }
            }

            // B. Notifikasi ke Koordinator Guru Piket hari ini (jika ada)
            if ($jadwal->koordinator && $jadwal->koordinator->status_aktif) {
                $waService->notifyKoordinatorPiket(
                    $jadwal->koordinator,
                    $jadwal,
                    $infoSesi['petugas']
                );
            }
        }

        // 2. CEK SISWA YANG SEDANG KELUAR DAN MELEWATI BATAS WAKTU (OVERDUE)
        $overdueList = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->where('sampai_pulang', false)
            ->where('is_warned', false)
            ->whereNotNull('batas_waktu_kembali')
            ->where('batas_waktu_kembali', '<', now('Asia/Jakarta'))
            ->get();

        foreach ($overdueList as $dispensasi) {
            if ($dispensasi->isOverdue()) {
                // Kirim WA ke Siswa & Guru Piket yang sedang bertugas
                $waService->notifySiswaTerlambatDanGuruPiket($dispensasi, 'overdue');

                // Tandai is_warned = true & warned_at = now() agar tidak dispam setiap menit
                $dispensasi->markAsWarned();
            }
        }

        return self::SUCCESS;
    }
}