<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index()
    {
        return view('satpam.scan');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|json'
        ]);

        $qrData = json_decode($request->qr_data, true);
        
        // Validasi token
        $expectedToken = md5($qrData['id'] . $qrData['nomor_surat'] . 'SECRET_KEY_DISPENSI');
        if ($qrData['token'] !== $expectedToken) {
            return response()->json(['success' => false, 'message' => 'QR Code tidak valid!'], 400);
        }

        $dispensasi = Dispensasi::with('siswa')->find($qrData['id']);

        if (!$dispensasi) {
            return response()->json(['success' => false, 'message' => 'Dispensasi tidak ditemukan!'], 404);
        }

        // Cek apakah sudah digunakan
        if ($dispensasi->status === 'selesai') {
            return response()->json([
                'success' => false, 
                'message' => 'Dispensasi sudah digunakan (siswa sudah kembali)',
                'data' => $dispensasi
            ], 400);
        }

        // Proses scan
        if ($dispensasi->status === 'disetujui') {
            // Scan pertama: siswa keluar
            $dispensasi->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa keluar tercatat',
                'action' => 'keluar',
                'data' => $dispensasi
            ]);
        } elseif ($dispensasi->status === 'keluar') {
            // Scan kedua: siswa kembali
            $dispensasi->update([
                'status' => 'selesai',
                'waktu_kembali_aktual' => now(),
                'satpam_kembali_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa kembali tercatat',
                'action' => 'kembali',
                'data' => $dispensasi
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Status dispensasi tidak valid'], 400);
    }
}