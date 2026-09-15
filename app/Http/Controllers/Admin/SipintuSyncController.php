<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SipintuService;
use Illuminate\Support\Facades\Http;

class SipintuSyncController extends Controller
{
    public function __construct(
        private SipintuService $sipintuService
    ) {}

    /**
     * Sinkronkan Data Siswa dari SiPintu Gateway
     */
    public function syncSiswa()
    {
        $result = $this->sipintuService->syncSiswa();

        if ($result['success']) {
            return redirect()->back()->with([
                'success'    => $result['message'],
                'sync_stats' => $result['stats'] ?? null,
            ]);
        }

        return redirect()->back()->with([
            'error'      => $result['message'],
            'sync_stats' => $result['stats'] ?? null,
        ]);
    }

    /**
     * Sinkronkan Data Guru dari SiPintu Gateway
     */
    public function syncGuru()
    {
        $result = $this->sipintuService->syncGuru();

        if ($result['success']) {
            return redirect()->back()->with([
                'success'    => $result['message'],
                'sync_stats' => $result['stats'] ?? null,
            ]);
        }

        return redirect()->back()->with([
            'error'      => $result['message'],
            'sync_stats' => $result['stats'] ?? null,
        ]);
    }
    /**
     * Test Koneksi ke SiPintu Gateway
     */
    public function testSync()
    {
        try {
            $apiUrl = config('services.sipintu.api_url', 'https://sipintu.smkn1bangsri.sch.id');

            // Timeout diperpanjang jadi 10 detik
            $response = Http::timeout(10)->withoutVerifying()->get($apiUrl);

            if ($response->successful()) {
                // ✅ CATAT KE AUDIT LOG
                \App\Models\AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'test_connection_sipintu',
                    'table_name' => 'sipintu_gateway',
                    'record_id' => 0,
                    'old_value' => null,
                    'new_value' => [
                        'success' => true,
                        'message' => 'Koneksi berhasil',
                        'url' => $apiUrl,
                        'http_status' => $response->status(),
                    ],
                    'ip_address' => request()->ip(),
                ]);

                return redirect()->back()->with('success', '✅ Koneksi ke SiPintu Gateway BERHASIL.');
            }

            // ✅ CATAT KE AUDIT LOG (GAGAL)
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'test_connection_sipintu',
                'table_name' => 'sipintu_gateway',
                'record_id' => 0,
                'old_value' => null,
                'new_value' => [
                    'success' => false,
                    'message' => 'Gagal terhubung',
                    'url' => $apiUrl,
                    'http_status' => $response->status(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return redirect()->back()->with('error', '❌ Gagal terhubung ke SiPintu (HTTP Status: ' . $response->status() . ')');

        } catch (\Exception $e) {
            // ✅ CATAT KE AUDIT LOG (ERROR)
            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'test_connection_sipintu',
                'table_name' => 'sipintu_gateway',
                'record_id' => 0,
                'old_value' => null,
                'new_value' => [
                    'success' => false,
                    'message' => 'Error koneksi',
                    'url' => $apiUrl,
                    'error_message' => $e->getMessage(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return redirect()->back()->with('error', '❌ Error koneksi: ' . $e->getMessage());
        }
    }
}
