<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SipintuService;
use Illuminate\Http\Request;

class SipintuSyncController extends Controller
{
    public function __construct(private SipintuService $sipintuService) {}

    public function syncSiswa(Request $request)
    {
        $result = $this->sipintuService->syncSiswa();

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function syncGuru(Request $request)
    {
        $result = $this->sipintuService->syncGuru();

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

}
