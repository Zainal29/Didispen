<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\PrintHelper;

class PrintLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Ambil model dispensasi dari parameter route (biasanya bernama 'dispensasi')
        $dispensasi = $request->route('dispensasi');

        if (!$dispensasi) {
            return $next($request);
        }

        $user = auth()->user();
        $reason = null;

        // Tentukan validasi berdasarkan role pengguna
        if ($user->role === 'siswa') {
            $reason = PrintHelper::getStudentBlockReason($dispensasi);
        } elseif (in_array($user->role, ['guru', 'admin'])) {
            $reason = PrintHelper::getTeacherBlockReason($dispensasi);
        }

        // Jika ada alasan penolakan, redirect kembali dengan pesan error
        if ($reason) {
            return redirect()->back()->with('error', $reason);
        }

        return $next($request);
    }
}
