<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SipintuService;
use Illuminate\Http\Request;

class SipintuSyncController extends Controller
{
    public function __construct(private SipintuService $sipintuService) {}

    /**
     * Sinkronkan Data Siswa dari SiPintu Gateway
     */
    public function syncSiswa(Request $request)
    {
        $force  = $request->boolean('force', false);
        $result = $this->sipintuService->syncSiswa($force);

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
    public function syncGuru(Request $request)
    {
        $force  = $request->boolean('force', false);
        $result = $this->sipintuService->syncGuru($force);

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
