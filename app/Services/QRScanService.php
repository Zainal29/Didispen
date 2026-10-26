<?php

namespace App\Services;

use App\Models\Dispensasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QRScanService
{
    public function __construct(
        private ?AuditLogService $auditLogService = null
    ) {
        $this->auditLogService = $auditLogService ?? app(AuditLogService::class);
    }
    private function baseQuery()
    {
        return Dispensasi::with([
            'siswa.kelas.jurusan',
            'siswa.user',
            'anggotaSiswa.kelas.jurusan',
            'anggotaSiswa.user',
            'guru',
        ]);
    }

    /**
     * Parse QR data dan mencari data dispensasi.
     *
     * Format yang didukung:
     * 1. JSON: {"token":"..."}
     * 2. Token langsung
     * 3. URL: /verify-qr/{id}
     * 4. URL: /verifikasi/{id}
     * 5. URL: /verifikasi/{id}?token=...
     */
    public function parseQRData(string $input): ?Dispensasi
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        /*
         * 1. JSON
         */
        if (json_validate($input)) {
            $qrData = json_decode($input, true);

            if (is_array($qrData)) {
                if (!empty($qrData['token']) && is_string($qrData['token'])) {
                    $d = $this->findByToken(trim($qrData['token']));
                    if ($d) return $d;
                }
                if (!empty($qrData['id']) && is_numeric($qrData['id'])) {
                    $d = $this->baseQuery()->find((int) $qrData['id']);
                    if ($d) return $d;
                }
                if (!empty($qrData['nomor_surat']) && is_string($qrData['nomor_surat'])) {
                    $d = $this->baseQuery()->where('nomor_surat', trim($qrData['nomor_surat']))->first();
                    if ($d) return $d;
                }
            }
        }

        /*
         * 2. URL (dengan scheme atau tanpa scheme)
         */
        $urlToParse = $input;
        if (!preg_match('~^[a-zA-Z]+://~', $urlToParse) && (str_contains($urlToParse, '/') || str_contains($urlToParse, '?'))) {
            $urlToParse = 'http://localhost/' . ltrim($urlToParse, '/');
        }

        if (filter_var($urlToParse, FILTER_VALIDATE_URL)) {
            $url = parse_url($urlToParse);
            $query = [];
            if (isset($url['query'])) {
                parse_str($url['query'], $query);
            }

            // A. Prioritaskan query parameter 'token'
            if (!empty($query['token']) && is_string($query['token'])) {
                $dispensasi = $this->findByToken(trim($query['token']));
                if ($dispensasi) {
                    return $dispensasi;
                }
            }

            // B. Query parameter 'dispensasi' (misal: ?dispensasi=16)
            if (!empty($query['dispensasi']) && is_numeric($query['dispensasi'])) {
                $dispensasi = $this->baseQuery()->find((int) $query['dispensasi']);
                if ($dispensasi) {
                    return $dispensasi;
                }
            }

            // C. Path URL dengan ID (misal: /verifikasi/16, /verify-qr/16)
            $path = $url['path'] ?? '';
            if (preg_match('~/(?:verify-qr|verifikasi|dispensasi)/(\d+)~i', $path, $matches)) {
                $dispensasi = $this->baseQuery()->find((int) $matches[1]);
                if ($dispensasi) {
                    return $dispensasi;
                }
            }
        }

        /*
         * 3. Token langsung (Alfanumerik, UUID, dengan dash/underscore, 16 - 64 karakter)
         */
        if (preg_match('/^[A-Za-z0-9\-_]{16,64}$/', $input)) {
            $dispensasi = $this->findByToken($input);
            if ($dispensasi) {
                return $dispensasi;
            }
        }

        /*
         * 4. Nomor surat langsung (misal: DISP/20261004/FFF5C5)
         */
        $byNomorSurat = $this->baseQuery()
            ->where('nomor_surat', $input)
            ->first();
        if ($byNomorSurat) {
            return $byNomorSurat;
        }

        /*
         * 5. ID angka langsung
         */
        if (is_numeric($input)) {
            $byId = $this->baseQuery()->find((int) $input);
            if ($byId) {
                return $byId;
            }
        }

        return null;
    }

    /**
     * Cek cooldown scan untuk mencegah double scan.
     */
    public function checkScanCooldown(Dispensasi $dispensasi): array
    {
        $cacheKey = 'scan_cooldown_disp_' . $dispensasi->id;
        if (! app()->runningUnitTests() && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return [
                'cooldown' => true,
                'message' => 'Dispensasi ini baru saja diproses. Mohon tunggu beberapa detik sebelum scan berikutnya.',
            ];
        }

        return [
            'cooldown' => false,
            'message' => null,
        ];
    }

    /**
     * Tandai cooldown scan untuk suatu dispensasi.
     */
    public function markScanCooldown(Dispensasi $dispensasi, int $seconds = 5): void
    {
        if (! app()->runningUnitTests()) {
            \Illuminate\Support\Facades\Cache::put('scan_cooldown_disp_' . $dispensasi->id, true, now()->addSeconds($seconds));
        }
    }

    /**
     * Simpan log scan.
     */
    public function logScan(
        int $dispensasiId,
        int $satpamId,
        string $action,
        bool $success,
        ?string $errorMessage = null
    ): void {
        try {
            DB::table('scan_logs')->insert([
                'dispensasi_id' => $dispensasiId,
                'satpam_id' => $satpamId,
                'action' => $action,
                'is_success' => $success,
                'error_message' => $errorMessage,
                'scanned_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal mencatat scan log: ' . $e->getMessage()
            );
        }
    }

    /**
     * Scan pertama:
     *
     * disetujui -> keluar
     */
    public function processKeluar(Dispensasi $dispensasi, int $userId): array
    {
        // 1. Validasi Status
        if ($dispensasi->status !== 'disetujui') {
            return [
                'success' => false,
                'message' => 'Dispensasi tidak dapat diproses keluar karena status sudah ' . strtoupper($dispensasi->status) . '.',
                'status_code' => 400,
            ];
        }

        // 2. Validasi Tanggal Izin
        $today = now('Asia/Jakarta')->toDateString();
        $tglIzin = ($dispensasi->tanggal_izin ?? $dispensasi->created_at)?->toDateString() ?? $today;

        if ($tglIzin > $today) {
            return [
                'success' => false,
                'message' => 'Dispensasi ini dijadwalkan untuk tanggal ' . Carbon::parse($tglIzin)->translatedFormat('l, d F Y') . '. Belum dapat digunakan hari ini.',
                'status_code' => 400,
            ];
        }

        if ($tglIzin < $today) {
            return [
                'success' => false,
                'message' => 'Dispensasi ini sudah KADALUARSA (berlaku tanggal ' . Carbon::parse($tglIzin)->translatedFormat('l, d F Y') . ').',
                'status_code' => 400,
            ];
        }

        $waktuAktual = \App\Helpers\TimeHelper::getWaktuAktual(
            $dispensasi->jam_kembali
        );
        $batasWaktu = $this->resolveBatasWaktu($dispensasi->jam_kembali, $waktuAktual);

        $updated = DB::transaction(function () use ($dispensasi, $batasWaktu, $userId) {
            return Dispensasi::whereKey($dispensasi->id)
                ->where('status', 'disetujui')
                ->lockForUpdate()
                ->update([
                    'status' => 'keluar',
                    'waktu_keluar_aktual' => now(),
                    'satpam_keluar_id' => $userId,
                    'batas_waktu_kembali' => $batasWaktu,
                ]);
        });

        if ($updated !== 1) {
            return [
                'success' => false,
                'message' => 'Gagal memproses keluar. Status mungkin sudah berubah.',
                'status_code' => 400,
            ];
        }

        $this->auditLogService->log(
            $userId,
            'qr_keluar',
            'dispensasi',
            $dispensasi->id,
            ['status' => 'disetujui'],
            ['status' => 'keluar', 'satpam_keluar_id' => $userId]
        );

        $this->markScanCooldown($dispensasi, 5);

        $msg = $dispensasi->sampai_pulang
            ? 'Siswa / Rombongan berhasil diverifikasi KELUAR sekolah (Izin Sampai Pulang - tidak perlu scan kembali).'
            : 'Siswa berhasil diverifikasi KELUAR sekolah.';

        return [
            'success' => true,
            'message' => $msg,
            'action' => 'keluar',
            'data' => $dispensasi->fresh(['siswa.kelas.jurusan', 'anggotaSiswa.kelas.jurusan']),
        ];
    }

    /**
     * Scan kedua:
     *
     * keluar -> selesai
     */
    public function processKembali(
        Dispensasi $dispensasi,
        int $userId,
        array $rombonganData = []
    ): array {
        if ($dispensasi->status !== 'keluar') {
            return [
                'success' => false,
                'message' => 'Dispensasi tidak dapat diproses kembali karena status sudah berubah (' . strtoupper($dispensasi->status) . ').',
                'status_code' => 400,
            ];
        }

        if ($dispensasi->sampai_pulang) {
            return [
                'success' => false,
                'message' => 'Dispensasi ini berizin Sampai Pulang Sekolah. Siswa tidak perlu melakukan scan kembali.',
                'status_code' => 400,
            ];
        }

        // Proteksi double scan: Berikan jeda 5 detik setelah keluar sebelum bisa scan kembali
        if ($dispensasi->waktu_keluar_aktual && ! app()->runningUnitTests()) {
            $now = now('Asia/Jakarta');
            $waktuKeluar = Carbon::parse($dispensasi->waktu_keluar_aktual)->setTimezone('Asia/Jakarta');
            $selisih = (int) $waktuKeluar->diffInSeconds($now, false);

            if ($selisih >= 0 && $selisih < 5) {
                $sisa = 5 - $selisih;
                return [
                    'success' => false,
                    'message' => "Siswa baru saja diverifikasi KELUAR ({$selisih} detik lalu). Mohon tunggu {$sisa} detik lagi sebelum memverifikasi KEMBALI untuk mencegah double scan.",
                    'status_code' => 422,
                ];
            }
        }

        $toleransiMenit = Dispensasi::TOLERANSI_KETERLAMBATAN_MENIT;
        $batasToleransi = $dispensasi->batas_waktu_kembali
            ? $dispensasi->batas_waktu_kembali->copy()->addMinutes($toleransiMenit)
            : null;

        $isLate = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
        $isWithinTolerance = $isLate && $batasToleransi && now()->lessThanOrEqualTo($batasToleransi);
        $isEarly = $dispensasi->batas_waktu_kembali && now()->lessThan($dispensasi->batas_waktu_kembali);

        /*
         * Setelah status menjadi 'selesai', file fisik (QR code, foto verifikasi,
         * dan foto bukti) dihapus untuk menghemat kapasitas storage server.
         * Record database dan histori dispensasi tetap dipertahankan secara utuh.
         */

        $updated = DB::transaction(function () use (
            $dispensasi,
            $userId,
            $isLate
        ) {
            return Dispensasi::whereKey($dispensasi->id)
                ->where('status', 'keluar')
                ->lockForUpdate()
                ->update([
                    'status' => 'selesai',
                    'waktu_kembali_aktual' => now(),
                    'satpam_kembali_id' => $userId,

                    /*
                     * Jika terlambat, tandai peringatan.
                     * Jika tidak terlambat, jangan menghapus
                     * histori peringatan yang mungkin sudah ada.
                     */
                    'is_warned' => $isLate
                        ? true
                        : $dispensasi->is_warned,

                    'warned_at' => $isLate
                        ? now()
                        : $dispensasi->warned_at,
                ]);
        });

        if ($updated !== 1) {
            return [
                'success' => false,
                'message' => 'Gagal memproses kembali.',
                'status_code' => 400,
            ];
        }

        // Refresh model agar statusnya 'selesai' sebelum cleanup dipanggil
        $dispensasi->refresh();

        // Pemrosesan status anggota rombongan & temuan satpam jika ada yang tidak kembali
        $laporanTemuanCount = 0;
        if ($dispensasi->isRombongan()) {
            $siswaKembaliIds = !empty($rombonganData['siswa_kembali_ids'])
                ? array_map('intval', (array) $rombonganData['siswa_kembali_ids'])
                : null; // null jika tidak dikirim (misal auto/tanpa checklist, dianggap kembali semua)

            $siswaTidakKembali = !empty($rombonganData['siswa_tidak_kembali'])
                ? (array) $rombonganData['siswa_tidak_kembali']
                : [];

            $dispensasi->loadMissing(['anggotaSiswa.kelas', 'siswa.kelas']);

            // 1. Update status tiap anggota rombongan
            foreach ($dispensasi->anggotaSiswa as $anggota) {
                $isKembali = true;
                $alasan = null;
                $catatan = null;

                $itemTidakKembali = collect($siswaTidakKembali)->firstWhere('siswa_id', $anggota->id);
                if ($itemTidakKembali || ($siswaKembaliIds !== null && !in_array($anggota->id, $siswaKembaliIds))) {
                    $isKembali = false;
                    $alasan = $itemTidakKembali['alasan'] ?? 'Tidak ikut kembali bersama rombongan';
                    $catatan = $itemTidakKembali['catatan'] ?? null;
                }

                if ($isKembali) {
                    $dispensasi->anggotaSiswa()->updateExistingPivot($anggota->id, [
                        'status_kembali' => 'kembali',
                        'catatan_kembali' => null,
                        'waktu_kembali' => now(),
                    ]);
                } else {
                    $dispensasi->anggotaSiswa()->updateExistingPivot($anggota->id, [
                        'status_kembali' => 'tidak_kembali',
                        'catatan_kembali' => $catatan ?: $alasan,
                        'waktu_kembali' => null,
                    ]);

                    \App\Models\LaporanSatpam::create([
                        'satpam_id' => $userId,
                        'siswa_id' => $anggota->id,
                        'dispensasi_id' => $dispensasi->id,
                        'kategori' => 'rombongan_tidak_kembali',
                        'keterangan' => "Siswa tidak ikut kembali bersama rombongan (No. Surat: {$dispensasi->nomor_surat}). Keterangan satpam: " . ($catatan ?: $alasan),
                        'status' => 'menunggu',
                    ]);
                    $laporanTemuanCount++;
                }
            }

            // 2. Cek apakah ketua (pemohon utama) juga tercatat tidak kembali
            $ketuaTidakKembali = collect($siswaTidakKembali)->firstWhere('siswa_id', $dispensasi->siswa_id);
            if ($ketuaTidakKembali || ($siswaKembaliIds !== null && !in_array($dispensasi->siswa_id, $siswaKembaliIds))) {
                $alasanKetua = $ketuaTidakKembali['alasan'] ?? 'Ketua/pemohon dispensasi tidak ikut kembali bersama rombongan';
                $catatanKetua = $ketuaTidakKembali['catatan'] ?? null;

                \App\Models\LaporanSatpam::create([
                    'satpam_id' => $userId,
                    'siswa_id' => $dispensasi->siswa_id,
                    'dispensasi_id' => $dispensasi->id,
                    'kategori' => 'rombongan_tidak_kembali',
                    'keterangan' => "Ketua rombongan tidak ikut kembali (No. Surat: {$dispensasi->nomor_surat}). Keterangan satpam: " . ($catatanKetua ?: $alasanKetua),
                    'status' => 'menunggu',
                ]);
                $laporanTemuanCount++;
            }

            // 3. Notifikasi ke Admin & Guru Piket jika ada temuan
            if ($laporanTemuanCount > 0) {
                try {
                    $notifService = app(\App\Services\NotifikasiService::class);
                    $adminUsers = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->get();
                    $notifText = "⚠️ Laporan Satpam: {$laporanTemuanCount} siswa rombongan ({$dispensasi->nomor_surat}) tidak kembali ke sekolah.";
                    foreach ($adminUsers as $admin) {
                        $notifService->send($admin->id, $notifText, route('admin.laporan-satpam.index'));
                    }
                    if ($dispensasi->guru?->user_id) {
                        $notifService->send($dispensasi->guru->user_id, $notifText, route('guru.laporan-satpam.index'));
                    }
                } catch (\Throwable $ne) {
                    Log::warning('Gagal kirim notifikasi laporan temuan satpam: ' . $ne->getMessage());
                }
            }
        }

        // Cleanup file fisik (QR code, foto_verifikasi, foto_bukti) setelah status menjadi selesai
        DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);

        $this->auditLogService->log(
            $userId,
            'qr_kembali',
            'dispensasi',
            $dispensasi->id,
            ['status' => 'keluar'],
            ['status' => 'selesai', 'satpam_kembali_id' => $userId, 'is_terlambat' => $isLate, 'laporan_temuan_count' => $laporanTemuanCount]
        );

        $message = $isLate
            ? ($isWithinTolerance
                ? 'Siswa berhasil dicatat KEMBALI. CATATAN: Terlambat (dalam toleransi antrean gerbang).'
                : 'Siswa berhasil dicatat KEMBALI. PERINGATAN: Terlambat dari batas waktu!')
            : ($isEarly
                ? 'Siswa berhasil dicatat KEMBALI (Lebih Awal).'
                : 'Siswa berhasil dicatat KEMBALI (Tepat Waktu).');

        if ($laporanTemuanCount > 0) {
            $message .= " Ditemukan {$laporanTemuanCount} siswa tidak kembali dan telah dilaporkan ke Admin & Guru Piket.";
        }

        $this->markScanCooldown($dispensasi, 5);

        return [
            'success' => true,
            'message' => $message,
            'action' => 'kembali',
            'is_terlambat' => $isLate,
            'laporan_temuan_count' => $laporanTemuanCount,
            'data' => $dispensasi->fresh([
                'siswa.kelas.jurusan',
                'anggotaSiswa.kelas.jurusan',
            ]),
        ];
    }

    /**
     * Cari dispensasi berdasarkan QR token.
     */
    private function findByToken(string $token): ?Dispensasi
    {
        return Dispensasi::with([
            'siswa.kelas.jurusan',
        ])
            ->where('qr_token', $token)
            ->first();
    }

    /**
     * Menentukan batas waktu kembali dengan berbagai format waktu yang fleksibel.
     * Mencegah uncaught exception dan menangani format direct time, range, "pulang", maupun periode.
     */
    public function resolveBatasWaktu(?string $jamKembali, ?string $waktuAktual = null): Carbon
    {
        $jamKembali = trim((string) $jamKembali);
        $waktuAktual = trim((string) $waktuAktual);

        $timezone = 'Asia/Jakarta';

        // 1. Sampai pulang -> gunakan akhir KBM yang dikonfigurasi admin
        if (
            stripos($jamKembali, 'pulang') !== false ||
            stripos($waktuAktual, 'pulang') !== false
        ) {
            return \App\Helpers\TimeHelper::getWaktuSelesaiKbmTerakhir()
                ?? now($timezone)->setTime(15, 30);
        }

        // 2. Format rentang waktu:
        //    08:30 - 09:15
        //    08.30 - 09.15
        foreach ([$waktuAktual, $jamKembali] as $str) {
            if ($str === '' || $str === '-') {
                continue;
            }

            if (preg_match(
                '/(\d{1,2})[:.](\d{2})\s*-\s*(\d{1,2})[:.](\d{2})/',
                $str,
                $m
            )) {
                return now($timezone)->setTime(
                    (int) $m[3],
                    (int) $m[4],
                    0
                );
            }
        }

        // 3. Format waktu langsung:
        //    08:28
        //    08.28
        foreach ([$jamKembali, $waktuAktual] as $str) {
            if ($str === '' || $str === '-') {
                continue;
            }

            if (preg_match(
                '/^(\d{1,2})[:.](\d{2})(?::00)?$/',
                $str,
                $m
            )) {
                return now($timezone)->setTime(
                    (int) $m[1],
                    (int) $m[2],
                    0
                );
            }
        }

        // 4. Format "Jam Pelajaran ke-X"
        if ($jamKembali !== '') {
            $parsed = \App\Helpers\TimeHelper::getBatasWaktuKembali(
                $jamKembali
            );

            if ($parsed instanceof Carbon) {
                return $parsed->copy()->setTimezone($timezone);
            }
        }

        // 5. Format tidak dikenali -> log dan gunakan akhir KBM
        Log::warning(
            "Batas waktu kembali tidak dapat diparsing sempurna: " .
            "jamKembali='{$jamKembali}', " .
            "waktuAktual='{$waktuAktual}'. " .
            "Menggunakan fallback akhir KBM."
        );

        return \App\Helpers\TimeHelper::getWaktuSelesaiKbmTerakhir()
            ?? now($timezone)->addHours(2);
    }
}
