<?php

namespace App\Services;

use App\Models\Dispensasi;
use Illuminate\Support\Facades\Log;

class QRScanService
{
    /**
     * Parse QR data dan return Dispensasi model
     */
    public function parseQRData(string $input): ?Dispensasi
    {
        $input = trim($input);
        $dispensasi = null;

        // 1. JSON format: {"token": "..."}
        if (json_validate($input)) {
            $qrData = json_decode($input, true);
            if (isset($qrData['token']) && is_string($qrData['token'])) {
                $dispensasi = $this->findByToken($qrData['token']);
            }
        }
        // 2. Pure token (64 chars)
        elseif (preg_match('/^[A-Za-z0-9]{64}$/', $input)) {
            $dispensasi = $this->findByToken($input);
        }
        // 3. URL format: /verify-qr/{id}?token=...
        elseif (preg_match('#/verify-qr/(\d+)#', $input, $matches)) {
            $id = (int) $matches[1];
            parse_str(parse_url($input, PHP_URL_QUERY) ?? '', $queryParams);
            $token = $queryParams['token'] ?? null;

            if ($token) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])
                    ->where('id', $id)
                    ->where('qr_token', $token)
                    ->first();
            }
        }
        // 4. Format legacy /verifikasi/{id} - TOLAK karena tidak aman
        elseif (preg_match('#/verifikasi/(\d+)#', $input, $matches)) {
            Log::warning('Format QR lama terdeteksi (tidak aman): ' . $input);
            return null;
        }

        return $dispensasi;
    }

    /**
     * <i class="fas fa-check-circle"></i> LAYER 1: Cek cooldown sebelum scan (DIPINDAHKAN KE LEVEL CLASS)
     */
    public function checkScanCooldown(Dispensasi $dispensasi): array
    {
        $now = now();
        $lastScanTime = null;

        if ($dispensasi->status === 'disetujui') {
            $lastScanTime = $dispensasi->waktu_keluar_aktual;
        } elseif ($dispensasi->status === 'keluar') {
            $lastScanTime = $dispensasi->waktu_kembali_aktual;
        }

        if ($lastScanTime && $now->diffInSeconds($lastScanTime) < 5) {
            return [
                'cooldown' => true,
                'remaining_seconds' => 5 - $now->diffInSeconds($lastScanTime),
                'message' => '⏱️ QR Code baru saja di-scan! Tunggu ' . round(5 - $now->diffInSeconds($lastScanTime)) . ' detik sebelum scan ulang.'
            ];
        }

        return ['cooldown' => false];
    }

    /**
     * Process scan pertama: disetujui → keluar
     */
    public function processKeluar(Dispensasi $dispensasi, int $userId): array
    {
        // <i class="fas fa-check-circle"></i> CEK COOLDOWN DULU
        $cooldown = $this->checkScanCooldown($dispensasi);
        if ($cooldown['cooldown']) {
            return [
                'success' => false,
                'message' => $cooldown['message'],
                'status_code' => 429, // Too Many Requests
            ];
        }

        $updated = Dispensasi::whereKey($dispensasi->id)
            ->where('status', 'disetujui')
            ->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => $userId,
            ]);

        if ($updated !== 1) {
            return [
                'success' => false,
                'message' => 'Gagal memproses. Status dispensasi sudah berubah.',
                'status_code' => 400,
            ];
        }

        $isSampaiPulang = str_contains(strtolower($dispensasi->jam_kembali), 'ke-9') ||
                         str_contains(strtolower($dispensasi->jam_kembali), 'ke-10');

        $pesan = 'Siswa berhasil dicatat KELUAR.';
        if ($isSampaiPulang) {
            $pesan .= ' Dispensasi berlaku sampai pulang sekolah.';
        } else {
            $pesan .= ' Wajib scan kembali saat siswa tiba di sekolah.';
        }

        return [
            'success' => true,
            'message' => $pesan,
            'action' => 'keluar',
            'is_sampai_pulang' => $isSampaiPulang,
            'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
        ];
    }

    /**
     * Process scan kedua: keluar → selesai
     */
    public function processKembali(Dispensasi $dispensasi, int $userId): array
    {
        // <i class="fas fa-check-circle"></i> CEK COOLDOWN DULU
        $cooldown = $this->checkScanCooldown($dispensasi);
        if ($cooldown['cooldown']) {
            return [
                'success' => false,
                'message' => $cooldown['message'],
                'status_code' => 429,
            ];
        }

        $isLate = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);

        $updated = Dispensasi::whereKey($dispensasi->id)
            ->where('status', 'keluar')
            ->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => $userId,
                'is_warned' => $isLate ? true : $dispensasi->is_warned,
                'warned_at' => $isLate ? now() : $dispensasi->warned_at,
            ]);

        if ($updated !== 1) {
            return [
                'success' => false,
                'message' => 'Gagal memproses kembali.',
                'status_code' => 400,
            ];
        }

        $pesan = $isLate
            ? 'Siswa berhasil dicatat KEMBALI (TERLAMBAT).'
            : 'Siswa berhasil dicatat KEMBALI (Tepat Waktu).';

        return [
            'success' => true,
            'message' => $pesan,
            'action' => 'kembali',
            'is_terlambat' => $isLate,
            'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
        ];
    }

    private function findByToken(string $token): ?Dispensasi
    {
        return Dispensasi::with(['siswa.kelas.jurusan'])
            ->where('qr_token', $token)
            ->first();
    }
}
