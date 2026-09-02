<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('guru.scan');
    }

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
                'message' => 'QR Code tidak ditemukan atau tidak valid! Pastikan formatnya benar.'
            ], 404);
        }

        // 2. Logika Scan PERTAMA: KELUAR
        if ($dispensasi->status === 'disetujui') {
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'disetujui')->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id(),
            ]);

            if ($updated !== 1) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses. Status dispensasi sudah berubah.'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Siswa berhasil dicatat KELUAR.',
                'action' => 'keluar',
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan'])
            ]);
        }

        // 3. Logika Scan KEDUA: KEMBALI
        if ($dispensasi->status === 'keluar') {
            $isLate = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);

            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'keluar')->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => auth()->id(),
                'is_warned' => $isLate ? true : $dispensasi->is_warned,
                'warned_at' => $isLate ? now() : $dispensasi->warned_at,
            ]);

            if ($updated !== 1) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses kembali.'], 400);
            }

            $pesan = $isLate ? '✅ Siswa berhasil dicatat KEMBALI (TERLAMBAT).' : '✅ Siswa berhasil dicatat KEMBALI (Tepat Waktu).';

            return response()->json([
                'success' => true,
                'message' => $pesan,
                'action' => 'kembali',
                'is_terlambat' => $isLate,
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan'])
            ]);
        }

        // 4. Jika status sudah selesai atau lainnya
        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses atau status tidak valid (Status: ' . ucfirst($dispensasi->status) . ').',
            'data' => $dispensasi,
        ], 400);
    }
}
