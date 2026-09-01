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
            ]);
        }

        // 2. Logika Scan PERTAMA: KELUAR
        if ($dispensasi->status === 'disetujui') {
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'disetujui')->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id(), // Mencatat siapa yang scan (bisa guru/satpam)
            ]);

            if ($updated !== 1) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses. Status dispensasi sudah berubah.']);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Siswa berhasil dicatat KELUAR (via Guru Piket)',
                'action' => 'keluar',
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan'])
            ]);
        }

        // 3. Logika Scan KEDUA: KEMBALI
        if ($dispensasi->status === 'keluar') {
            $updated = Dispensasi::whereKey($dispensasi->id)->where('status', 'keluar')->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => auth()->id(),
            ]);

            if ($updated !== 1) {
                return response()->json(['success' => false, 'message' => 'Gagal memproses kembali.']);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Siswa berhasil dicatat KEMBALI (via Guru Piket)',
                'action' => 'kembali',
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan'])
            ]);
        }

        // 4. Jika status sudah selesai atau lainnya
        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses atau status tidak valid.',
            'data' => $dispensasi,
        ]);
    }
}
