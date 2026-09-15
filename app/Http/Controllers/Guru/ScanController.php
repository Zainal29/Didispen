<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Services\QRScanService;
use App\Services\NotifikasiService; // ✅ TAMBAHKAN INI
use Illuminate\Http\Request;

class ScanController extends Controller
{

    public function __construct(
           private NotifikasiService $notifikasiService // ✅ INJEKSI SERVICE
       ) {}

    /**
     * Tampilkan halaman scan QR
     */
    public function index()
    {
        return view('guru.scan');
    }

    /**
     * Proses verifikasi data QR Code
     */
    public function verify(Request $request, QRScanService $scanService)
    {
        $request->validate(['qr_data' => 'required|string']);

        $dispensasi = $scanService->parseQRData($request->qr_data);

        if (!$dispensasi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan atau tidak valid! Pastikan formatnya benar.'
            ], 404);
        }

        if ($dispensasi->status === 'disetujui') {
            $result = $scanService->processKeluar($dispensasi, auth()->id());
            return response()->json($result, $result['status_code'] ?? 200);
        }

        if ($dispensasi->status === 'keluar') {
            $result = $scanService->processKembali($dispensasi, auth()->id());
            return response()->json($result, $result['status_code'] ?? 200);
        }

        // ✅ TAMBAHKAN INI: Kirim Notifikasi Selesai
                   $this->notifikasiService->send(
                       $dispensasi->siswa->user_id,
                       "Dispensasi Anda ({$dispensasi->nomor_surat}) telah SELESAI. Anda telah kembali ke sekolah dengan selamat.",
                       route('siswa.pengajuan.show', $dispensasi->id)
                   );

        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses atau status tidak valid (Status: ' . ucfirst($dispensasi->status) . ').',
            'data' => $dispensasi,
        ], 400);
    }
}
