<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('satpam.scan');
    }

    public function verify(Request $request)
    {
        $request->validate(['qr_data' => 'required|string']);
        $input = trim($request->qr_data);
        $dispensasi = null;

        // 1. Parsing QR Data (sama seperti sebelumnya)
        if (json_validate($input)) {
            $qrData = json_decode($input, true);
            if (isset($qrData['token']) && is_string($qrData['token'])) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('qr_token', $qrData['token'])->first();
            }
        } elseif (preg_match('/^[A-Za-z0-9]{64}$/', $input)) {
            $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('qr_token', $input)->first();
        } elseif (preg_match('#/verifikasi/(\d+)$#', $input)) {
            $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('id', (int) preg_replace('#\D+#', '', $input))->whereNotNull('qr_token')->first();
        }

        if (! $dispensasi) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak ditemukan atau tidak valid!']);
        }

        // 2. LOGIKA SCAN PERTAMA: KELUAR (disetujui -> keluar)
        if ($dispensasi->status === 'disetujui') {
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'disetujui')->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id(),
            ]);

            if ($updated !== 1) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses. Status dispensasi berubah.']);
            }

            // ✅ DETEKSI "SAMPAI PULANG"
            $isSampaiPulang = str_contains(strtolower($dispensasi->jam_kembali), 'ke-9') || str_contains(strtolower($dispensasi->jam_kembali), 'ke-10');

            $pesanSukses = '✅ Siswa berhasil dicatat KELUAR.';
            if ($isSampaiPulang) {
                $pesanSukses .= ' Dispensasi berlaku sampai pulang sekolah. Tidak wajib scan kembali (akan otomatis selesai via sistem).';
            } else {
                $pesanSukses .= ' Wajib scan kembali saat siswa tiba di sekolah.';
            }

            app(NotifikasiService::class)->send(
                $dispensasi->siswa->user_id,
                "🚪 Dispensasi ({$dispensasi->nomor_surat}) telah di-scan (Siswa Keluar).",
                route('siswa.pengajuan.show', $dispensasi, false)
            );

            return response()->json([
                'success' => true,
                'message' => $pesanSukses,
                'action' => 'keluar',
                'is_sampai_pulang' => $isSampaiPulang, // Kirim flag ini ke frontend
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
            ]);
        }

        // 3. LOGIKA SCAN KEDUA: KEMBALI (keluar -> selesai)
        if ($dispensasi->status === 'keluar') {
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'keluar')->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => auth()->id(),
            ]);

            if ($updated !== 1) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses kembali.']);
            }

            // Cek keterlambatan untuk notifikasi
            $isTerlambat = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
            $pesanKembali = '✅ Siswa berhasil dicatat KEMBALI.';

            if ($isTerlambat) {
                $pesanKembali .= ' ⚠️ PERINGATAN: Siswa terlambat dari batas waktu yang ditentukan!';
                // Opsional: Auto-tandai sebagai warned jika terlambat
                $dispensasi->update(['is_warned' => true, 'warned_at' => now()]);
            }

            app(NotifikasiService::class)->send(
                $dispensasi->siswa->user_id,
                "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI (Siswa Kembali).",
                route('siswa.pengajuan.show', $dispensasi, false)
            );

            return response()->json([
                'success' => true,
                'message' => $pesanKembali,
                'action' => 'kembali',
                'is_terlambat' => $isTerlambat,
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan']),
            ]);
        }

        // 4. Jika status sudah 'selesai'
        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses (Siswa sudah kembali).',
            'data' => $dispensasi,
        ]);
    }
}
