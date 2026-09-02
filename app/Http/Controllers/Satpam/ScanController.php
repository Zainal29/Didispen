<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    /**
     * Tampilkan halaman scan QR
     */
    public function index()
    {
        return view('satpam.scan');
    }

    /**
     * Proses verifikasi data QR Code (dipanggil via AJAX dari frontend)
     */
    public function verify(Request $request)
    {
        $request->validate(['qr_data' => 'required|string']);
        $input = trim($request->qr_data);
        $dispensasi = null;

        // 1. Parsing QR Data (Mendukung JSON, Token Murni, DAN URL)
        if (json_validate($input)) {
            $qrData = json_decode($input, true);
            if (isset($qrData['token']) && is_string($qrData['token'])) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('qr_token', $qrData['token'])->first();
            }
        } elseif (preg_match('/^[A-Za-z0-9]{64}$/', $input)) {
            // Format: Token murni (64 karakter)
            $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('qr_token', $input)->first();
        } elseif (preg_match('#/verify-qr/(\d+)#', $input, $matches)) {
            // Format URL: /verify-qr/18?token=...
            $id = (int) $matches[1];
            parse_str(parse_url($input, PHP_URL_QUERY) ?? '', $queryParams);
            $token = $queryParams['token'] ?? null;

            if ($token) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('id', $id)->where('qr_token', $token)->first();
            } else {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('id', $id)->whereNotNull('qr_token')->first();
            }
        } elseif (preg_match('#/verifikasi/(\d+)#', $input, $matches)) {
            // Format URL alternatif: /verifikasi/18
            $id = (int) $matches[1];
            $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('id', $id)->whereNotNull('qr_token')->first();
        }

        if (! $dispensasi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan atau tidak valid!'
            ], 404);
        }

        // 2. LOGIKA SCAN PERTAMA: KELUAR (disetujui -> keluar)
        if ($dispensasi->status === 'disetujui') {
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'disetujui')->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id(),
            ]);

            if ($updated !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses. Status dispensasi sudah berubah.'
                ], 400);
            }

            $isSampaiPulang = str_contains(strtolower($dispensasi->jam_kembali), 'ke-9') || str_contains(strtolower($dispensasi->jam_kembali), 'ke-10');
            $pesanSukses = '✅ Siswa berhasil dicatat KELUAR.';

            if ($isSampaiPulang) {
                $pesanSukses .= ' Dispensasi berlaku sampai pulang sekolah.';
            } else {
                $pesanSukses .= ' Wajib scan kembali saat siswa tiba di sekolah.';
            }

            // Kirim notifikasi jika service tersedia
            if (class_exists(\App\Services\NotifikasiService::class)) {
                app(\App\Services\NotifikasiService::class)->send(
                    $dispensasi->siswa->user_id,
                    "🚪 Dispensasi ({$dispensasi->nomor_surat}) telah di-scan (Siswa Keluar).",
                    route('siswa.pengajuan.show', $dispensasi, false)
                );
            }

            return response()->json([
                'success' => true,
                'message' => $pesanSukses,
                'action' => 'keluar',
                'is_sampai_pulang' => $isSampaiPulang,
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
            ]);
        }

        // 3. LOGIKA SCAN KEDUA: KEMBALI (keluar -> selesai)
        if ($dispensasi->status === 'keluar') {
            $isTerlambat = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);

            // ✅ OPTIMASI: Update status dan flag keterlambatan dalam 1 query
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'keluar')->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => auth()->id(),
                'is_warned' => $isTerlambat ? true : $dispensasi->is_warned,
                'warned_at' => $isTerlambat ? now() : $dispensasi->warned_at,
            ]);

            if ($updated !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses kembali.'
                ], 400);
            }

            $pesanKembali = $isTerlambat
                ? '✅ Siswa berhasil dicatat KEMBALI. ⚠️ PERINGATAN: Terlambat dari batas waktu!'
                : '✅ Siswa berhasil dicatat KEMBALI (Tepat Waktu).';

            // Kirim notifikasi jika service tersedia
            if (class_exists(\App\Services\NotifikasiService::class)) {
                app(\App\Services\NotifikasiService::class)->send(
                    $dispensasi->siswa->user_id,
                    "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI (Siswa Kembali).",
                    route('siswa.pengajuan.show', $dispensasi, false)
                );
            }

            return response()->json([
                'success' => true,
                'message' => $pesanKembali,
                'action' => 'kembali',
                'is_terlambat' => $isTerlambat,
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
            ]);
        }

        // 4. Jika status sudah 'selesai', 'ditolak', atau lainnya
        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses atau status tidak valid (Status: ' . ucfirst($dispensasi->status) . ').',
            'data' => $dispensasi,
        ], 400);
    }
}
