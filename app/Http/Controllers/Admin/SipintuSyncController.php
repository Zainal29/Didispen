<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SipintuService;

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
}
