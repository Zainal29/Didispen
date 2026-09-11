<?php

namespace App\Services;

use App\Models\Dispensasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QRScanService
{
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
         *
         * Contoh:
         * {"token":"xxxxxxxx"}
         */
        if (json_validate($input)) {
            $qrData = json_decode($input, true);

            if (
                is_array($qrData)
                && isset($qrData['token'])
                && is_string($qrData['token'])
                && trim($qrData['token']) !== ''
            ) {
                return $this->findByToken(trim($qrData['token']));
            }
        }

        /*
         * 2. URL
         *
         * Contoh:
         * https://domain.com/verifikasi/123?token=abcdef
         * https://domain.com/verify-qr/123
         */
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $url = parse_url($input);

            $path = $url['path'] ?? '';
            $query = [];

            if (isset($url['query'])) {
                parse_str($url['query'], $query);
            }

            /*
             * Jika URL memiliki token, prioritaskan token.
             * Ini lebih aman daripada hanya menggunakan ID.
             */
            if (
                isset($query['token'])
                && is_string($query['token'])
                && trim($query['token']) !== ''
            ) {
                $dispensasi = $this->findByToken(trim($query['token']));

                if ($dispensasi) {
                    return $dispensasi;
                }
            }

            /*
             * Fallback berdasarkan ID pada URL.
             *
             * /verify-qr/123
             * /verifikasi/123
             */
            if (filter_var($input, FILTER_VALIDATE_URL)) {
                $url = parse_url($input);

                $path = $url['path'] ?? '';
                $query = [];

                if (isset($url['query'])) {
                    parse_str($url['query'], $query);
                }

                if (
                    isset($query['token'])
                    && is_string($query['token'])
                    && trim($query['token']) !== ''
                ) {
                    return $this->findByToken(trim($query['token']));
                }

                return null;
            }
        }

        /*
         * 3. URL/path tanpa scheme
         *
         * Contoh:
         * /verifikasi/123?token=abcdef
         * /verify-qr/123
         */
        // if (preg_match(
        //  '~/(?:verify-qr|verifikasi)/(\d+)(?:/)?(?:\?([^#]*))?$~i',
        //     $input,
        //     $matches
        // )) {
        //     $id = (int) $matches[1];

        //     if (! empty($matches[2])) {
        //         $query = [];

        //         parse_str($matches[2], $query);

        //         if (
        //             isset($query['token'])
        //             && is_string($query['token'])
        //             && trim($query['token']) !== ''
        //         ) {
        //             $dispensasi = $this->findByToken(trim($query['token']));

        //             if ($dispensasi) {
        //                 return $dispensasi;
        //             }
        //         }
        //     }

        //     return Dispensasi::with(['siswa.kelas.jurusan'])
        //         ->find($id);
        // }

        /*
         * 3. URL/path tanpa scheme
         *
         * Contoh yang valid:
         * /verifikasi/123?token=abcdef
         * /verify-qr/123?token=abcdef
         *
         * ID hanya digunakan sebagai bagian dari URL.
         * Verifikasi tetap wajib menggunakan token.
         */
        if (preg_match(
            '~/(?:verify-qr|verifikasi)/(\d+)(?:/)?(?:\?([^#]*))?$~i',
            $input,
            $matches
        )) {
            if (!empty($matches[2])) {
                $query = [];

                parse_str($matches[2], $query);

                if (
                    isset($query['token'])
                    && is_string($query['token'])
                    && trim($query['token']) !== ''
                ) {
                    return $this->findByToken(trim($query['token']));
                }
            }

            return null;
        }

        /*
         * 4. Token langsung
         *
         * Token QR project dapat berupa string alfanumerik.
         */
        if (preg_match('/^[A-Za-z0-9]{32,64}$/', $input)) {
            return $this->findByToken($input);
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
    public function processKeluar(
        Dispensasi $dispensasi,
        int $userId
    ): array {
        /*
         * Pastikan status masih disetujui.
         */
        if ($dispensasi->status !== 'disetujui') {
            return [
                'success' => false,
                'message' => 'Dispensasi tidak dapat diproses keluar karena status sudah berubah.',
                'status_code' => 400,
            ];
        }

        /*
         * Tentukan batas waktu kembali berdasarkan
         * jam pelajaran yang dipilih.
         *
         * TimeHelper hanya menyediakan:
         * getWaktuAktual("Jam Pelajaran ke-X")
         */
        $jamKembali = (int) $dispensasi->jam_kembali;

        $waktuAktual = \App\Helpers\TimeHelper::getWaktuAktual(
            'Jam Pelajaran ke-' . $jamKembali
        );

        /*
         * Format:
         * 07:00 - 07:45
         */
        $batasWaktu = $this->resolveBatasWaktu(
            $waktuAktual,
            $jamKembali
        );

        /*
         * Gunakan transaction + lock untuk mencegah
         * dua Satpam memproses QR yang sama bersamaan.
         */
        $updated = DB::transaction(function () use (
            $dispensasi,
            $batasWaktu,
            $userId
        ) {
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

        $fresh = $dispensasi->fresh([
            'siswa.kelas.jurusan',
        ]);

        return [
            'success' => true,
            'message' => 'Siswa berhasil diverifikasi KELUAR sekolah.',
            'action' => 'keluar',
            'data' => $fresh,
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
         * Jangan hapus file foto di sini.
         *
         * Foto merupakan bagian dari histori/bukti dispensasi.
         * Penghapusan file sebaiknya dilakukan oleh command
         * cleanup yang memang menangani retensi foto.
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
     * Mengubah hasil TimeHelper:
     *
     * "07:00 - 07:45"
     *
     * menjadi timestamp batas waktu kembali.
     */
    private function resolveBatasWaktu(
        string $waktuAktual,
        int $jamKembali
    ): Carbon {
        if ($waktuAktual !== '-' && str_contains($waktuAktual, '-')) {
            $parts = array_map(
                'trim',
                explode('-', $waktuAktual, 2)
            );

            if (isset($parts[1]) && preg_match('/^\d{2}:\d{2}$/', $parts[1])) {
                return now()->setTimeFromTimeString($parts[1]);
            }
        }

        /*
         * Fallback jika jam pelajaran tidak ditemukan.
         *
         * Ini hanya pengaman agar sistem tidak crash.
         */
        return now()->addHours(2);
    }
}
