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
        // 1. CEK HARI OPERASIONAL AKTIF & SESI PIKET
        $rawActiveDays = \App\Models\Setting::get('wa_active_days');
        $activeDays = $rawActiveDays !== null ? (json_decode($rawActiveDays, true) ?? []) : [1, 2, 3, 4, 5];
        $todayDayOfWeek = now('Asia/Jakarta')->dayOfWeekIso;

        if (in_array($todayDayOfWeek, $activeDays)) {
            $leadMinutes = (int) \App\Models\Setting::get('wa_piket_reminder_minutes', 15);
            $todayDateStr = now('Asia/Jakarta')->toDateString();
            $now = now('Asia/Jakarta');

            // Ambil semua sesi jadwal piket untuk hari/tanggal ini
            $jadwalHariIni = $piketService->getJadwalUntukTanggal($now);

            foreach ($jadwalHariIni as $index => $jadwal) {
                $sesiMulai = \Carbon\Carbon::parse("{$todayDateStr} {$jadwal->jam_mulai}", 'Asia/Jakarta');
                $sesiSelesai = \Carbon\Carbon::parse("{$todayDateStr} {$jadwal->jam_selesai}", 'Asia/Jakarta');

                $reminderStart = $sesiMulai->copy()->subMinutes($leadMinutes);

                // Window pengiriman pengingat: dari (jam_mulai - leadMinutes) sampai jam_selesai
                // Deduplikasi di WhatsappMessageService via referenceKey mencegah pengiriman ganda
                if ($now->greaterThanOrEqualTo($reminderStart) && $now->lessThan($sesiSelesai)) {
                    $petugasInfo = $piketService->getPetugasAktual($jadwal, $now);

                    if (! $petugasInfo['conflict']) {
                        // A. Notifikasi ke Guru Piket (Petugas Aktual: Resmi atau Pengganti yang disetujui)
                        foreach ($petugasInfo['petugas'] as $petugas) {
                            if ($petugas['guru'] && $petugas['guru']->status_aktif) {
                                $waService->notifyGuruPiketBertugas(
                                    $petugas['guru'],
                                    $jadwal,
                                    $petugas['is_pengganti']
                                );
                            }
                        }

                        // B. Notifikasi ke Koordinator Guru Piket hari ini (1x sehari di sesi pertama)
                        if ($index === 0 && $jadwal->koordinator && $jadwal->koordinator->status_aktif) {
                            $waService->notifyKoordinatorPiket(
                                $jadwal->koordinator,
                                $jadwal,
                                $petugasInfo['petugas']
                            );
                        }
                    }
                }
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