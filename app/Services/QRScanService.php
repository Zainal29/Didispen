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
         * Contoh:
         * {"token":"xxxxxxxx"}
         * {"id":16}
         * {"nomor_surat":"DISP/..."}
         */
        if (json_validate($input)) {
            $qrData = json_decode($input, true);

            if (is_array($qrData)) {
                if (!empty($qrData['token']) && is_string($qrData['token'])) {
                    $d = $this->findByToken(trim($qrData['token']));
                    if ($d) return $d;
                }
                if (!empty($qrData['id']) && is_numeric($qrData['id'])) {
                    $d = Dispensasi::with(['siswa.kelas.jurusan'])->find((int) $qrData['id']);
                    if ($d) return $d;
                }
                if (!empty($qrData['nomor_surat']) && is_string($qrData['nomor_surat'])) {
                    $d = Dispensasi::with(['siswa.kelas.jurusan'])->where('nomor_surat', trim($qrData['nomor_surat']))->first();
                    if ($d) return $d;
                }
            }
        }

        /*
         * 2. URL (dengan scheme atau tanpa scheme)
         * Contoh:
         * https://domain.com/verifikasi/123?token=abcdef
         * https://domain.com/verify-qr/123
         * /satpam/scan?dispensasi=16
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
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->find((int) $query['dispensasi']);
                if ($dispensasi) {
                    return $dispensasi;
                }
            }

            // C. Path URL dengan ID (misal: /verifikasi/16, /verify-qr/16)
            $path = $url['path'] ?? '';
            if (preg_match('~/(?:verify-qr|verifikasi|dispensasi)/(\d+)~i', $path, $matches)) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->find((int) $matches[1]);
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
        $byNomorSurat = Dispensasi::with(['siswa.kelas.jurusan'])
            ->where('nomor_surat', $input)
            ->first();
        if ($byNomorSurat) {
            return $byNomorSurat;
        }

        /*
         * 5. ID angka langsung
         */
        if (is_numeric($input)) {
            $byId = Dispensasi::with(['siswa.kelas.jurusan'])->find((int) $input);
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
        $recentScan = DB::table('scan_logs')
            ->where('dispensasi_id', $dispensasi->id)
            ->where('scanned_at', '>=', now()->subSeconds(5))
            ->first();

        if ($recentScan) {
            return [
                'cooldown' => true,
                'message' => 'QR Code baru saja di-scan. Tunggu beberapa detik untuk scan berikutnya.',
            ];
        }

        return [
            'cooldown' => false,
            'message' => null,
        ];
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
        if ($dispensasi->status !== 'disetujui') {
            return [
                'success' => false,
                'message' => 'Dispensasi tidak dapat diproses keluar karena status sudah berubah.',
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

        return [
            'success' => true,
            'message' => 'Siswa berhasil diverifikasi KELUAR sekolah.',
            'action' => 'keluar',
            'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
        ];
    }

    /**
     * Scan kedua:
     *
     * keluar -> selesai
     */
    public function processKembali(
        Dispensasi $dispensasi,
        int $userId
    ): array {
        if ($dispensasi->status !== 'keluar') {
            return [
                'success' => false,
                'message' => 'Dispensasi tidak dapat diproses kembali karena status sudah berubah.',
                'status_code' => 400,
            ];
        }

        $isLate = $dispensasi->batas_waktu_kembali
            && now()->greaterThan($dispensasi->batas_waktu_kembali);

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

        // Cleanup file fisik (QR code, foto_verifikasi, foto_bukti) setelah status menjadi selesai
        DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);

        $this->auditLogService->log(
            $userId,
            'qr_kembali',
            'dispensasi',
            $dispensasi->id,
            ['status' => 'keluar'],
            ['status' => 'selesai', 'satpam_kembali_id' => $userId, 'is_terlambat' => $isLate]
        );

        $message = $isLate
            ? 'Siswa berhasil dicatat KEMBALI. PERINGATAN: Terlambat dari batas waktu!'
            : 'Siswa berhasil dicatat KEMBALI (Tepat Waktu).';

        return [
            'success' => true,
            'message' => $message,
            'action' => 'kembali',
            'is_terlambat' => $isLate,
            'data' => $dispensasi->fresh([
                'siswa.kelas.jurusan',
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

        // 1. Dispensasi sampai pulang -> batas waktu hingga akhir KBM hari ini
        if (stripos($jamKembali, 'pulang') !== false || stripos($waktuAktual, 'pulang') !== false) {
            return \App\Helpers\TimeHelper::getWaktuSelesaiKbmTerakhir() ?? now()->setTime(15, 30);
        }

        // 2. Format rentang waktu 'HH:MM - HH:MM' atau 'HH.MM - HH.MM'
        foreach ([$waktuAktual, $jamKembali] as $str) {
            if ($str !== '' && $str !== '-') {
                if (preg_match('/(\d{1,2})[:.](\d{2})\s*-\s*(\d{1,2})[:.](\d{2})/', $str, $m)) {
                    return now()->setTime((int) $m[3], (int) $m[4], 0);
                }
            }
        }

        // 3. Format waktu langsung tunggal 'HH:MM' atau 'HH.MM' (misal: "08:28")
        foreach ([$jamKembali, $waktuAktual] as $str) {
            if ($str !== '' && $str !== '-') {
                if (preg_match('/^(\d{1,2})[:.](\d{2})(:00)?$/', $str, $m)) {
                    return now()->setTime((int) $m[1], (int) $m[2], 0);
                }
            }
        }

        // 4. Jam pelajaran ke-X via TimeHelper::getBatasWaktuKembali
        if ($jamKembali !== '') {
            $parsed = \App\Helpers\TimeHelper::getBatasWaktuKembali($jamKembali);
            if ($parsed instanceof Carbon) {
                return $parsed;
            }
        }

        // 5. Fallback aman jika semua deteksi di atas tidak cocok
        Log::warning("Batas waktu kembali tidak dapat diparsing sempurna: jamKembali='{$jamKembali}', waktuAktual='{$waktuAktual}'. Menggunakan fallback akhir KBM.");
        return \App\Helpers\TimeHelper::getWaktuSelesaiKbmTerakhir() ?? now()->addHours(2);
    }
}
