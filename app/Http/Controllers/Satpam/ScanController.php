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
            'qr_data' => 'required|string'
        ]);

        $input = trim($request->qr_data);
        $dispensasi = null;

        // 1. Cek jika input berupa JSON (format token aman)
        if (json_validate($input)) {
            $qrData = json_decode($input, true);
            if (isset($qrData['id'])) {
                $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])->find($qrData['id']);
            }
        } 
        
        // 2. Jika bukan JSON (atau ID dari JSON tidak ketemu), cari berdasarkan teks/nomor surat langsung
        if (!$dispensasi) {
            // Bersihkan jika QR berisi URL lengkap (misal: http://.../verifikasi/DISP/...)
            if (str_contains($input, '/verifikasi/')) {
                $segments = explode('/verifikasi/', $input);
                $input = end($segments);
            }

            $dispensasi = Dispensasi::with(['siswa.kelas.jurusan'])
                ->where('nomor_surat', 'LIKE', '%' . $input)
                ->orWhere('id', $input)
                ->first();
        }

        if (!$dispensasi) {
            return response()->json(['success' => false, 'message' => 'Dispensasi atau QR Code tidak ditemukan dalam sistem!'], 404);
        }

        // Cek apakah sudah pernah di-scan / digunakan
        if (in_array($dispensasi->status, ['keluar', 'selesai'])) {
            return response()->json([
                'success' => false, 
                'message' => 'QR Code ini sudah pernah di-scan dan tidak dapat di-scan lagi!',
                'data' => $dispensasi
            ], 400);
        }

        // Proses scan / verifikasi status (hanya jika status 'disetujui')
        if ($dispensasi->status === 'disetujui') {
            $dispensasi->update([
                'status' => 'keluar',
                'waktu_keluar_aktual' => now(),
                'satpam_keluar_id' => auth()->id()
            ]);

            // Kirim notifikasi ke siswa bahwa QR Code telah di-scan
            app(\App\Services\NotifikasiService::class)->send(
                $dispensasi->siswa->user_id,
                "🚪 Dispensasi ({$dispensasi->nomor_surat}) telah DI-SCAN oleh Satpam di pos gerbang (Siswa Keluar).",
                route('siswa.pengajuan.show', $dispensasi, false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Siswa keluar tercatat',
                'action' => 'keluar',
                'data' => $dispensasi->load(['siswa.kelas.jurusan'])
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Status dispensasi tidak valid untuk di-scan.'], 400);
    }
}