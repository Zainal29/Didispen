<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;
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

        // Parsing QR Data
        if (json_validate($input)) {
            $qrData = json_decode($input, true);
            if (isset($qrData['token'])) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('qr_token', $qrData['token'])->first();
            }
        } elseif (preg_match('/^[A-Za-z0-9]{64}$/', $input)) {
            $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->where('qr_token', $input)->first();
        }

        if (!$dispensasi) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak ditemukan!']);
        }

        // Logika Scan Keluar
        if ($dispensasi->status === 'disetujui') {
            $dispensasi->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id(), // Kita pakai kolom ini untuk mencatat siapa yang scan
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Siswa berhasil dicatat KELUAR (via Guru Piket)',
                'action' => 'keluar',
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan'])
            ]);
        }

        // Logika Scan Kembali
        if ($dispensasi->status === 'keluar') {
            $dispensasi->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => '✅ Siswa berhasil dicatat KEMBALI (via Guru Piket)',
                'action' => 'kembali',
                'data' => $dispensasi->fresh(['siswa.kelas.jurusan'])
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Status dispensasi tidak valid.']);
    }
}
