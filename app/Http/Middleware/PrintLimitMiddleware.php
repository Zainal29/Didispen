<?php

namespace App\Http\Middleware;

use App\Helpers\PrintHelper;
use App\Models\Dispensasi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintLimitMiddleware
{
    public function handle(Request $request, Closure $next, ?int $limit = null)
    {
        $id = $request->route('dispensasi');

        return DB::transaction(function () use ($request, $next, $id) {
            $dispensasi = Dispensasi::whereKey($id)->lockForUpdate()->first();

            if (! $dispensasi) {
                abort(404, 'Data dispensasi tidak ditemukan.');
            }

            $request->route()->setParameter('dispensasi', $dispensasi);

            // ✅ Validasi batas cetak + jam operasional lewat PrintHelper
            // agar KONSISTEN dengan panel Guru maupun Siswa (single source of truth).
            if ($reason = PrintHelper::blockReason($dispensasi)) {
                return redirect()->back()->with('error', $reason);
            }

            return $next($request);
        });
    }
}
