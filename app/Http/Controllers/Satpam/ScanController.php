<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Services\QRScanService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    /**
     * Tampilkan halaman scan QR.
     */
    public function index()
    {
        return view('satpam.scan');
    }

    /**
     * Proses verifikasi QR Code.
     */
    public function verify(Request $request, QRScanService $scanService)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        $dispensasi = $scanService->parseQRData($request->qr_data);

        if (! $dispensasi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau data dispensasi tidak ditemukan.',
            ], 404);
        }

        if ($dispensasi->status === 'disetujui') {
            $result = $scanService->processKeluar(
                $dispensasi,
                auth()->id()
            );

            return response()->json(
                $result,
                $result['status_code'] ?? 200
            );
        }

        if ($dispensasi->status === 'keluar') {
            $result = $scanService->processKembali(
                $dispensasi,
                auth()->id()
            );

            return response()->json(
                $result,
                $result['status_code'] ?? 200
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses atau status tidak valid (Status: '
                . ucfirst($dispensasi->status)
                . ').',
            'data' => $dispensasi,
        ], 400);
    }
}
