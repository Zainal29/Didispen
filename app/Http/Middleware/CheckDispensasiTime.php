<?php

namespace App\Http\Middleware;

use App\Helpers\DispensasiTimeHelper;
use Closure;
use Illuminate\Http\Request;

class CheckDispensasiTime
{
    public function handle(Request $request, Closure $next)
    {
        $timeCheck = DispensasiTimeHelper::isWithinDispensasiTime();

        if (! $timeCheck['allowed']) {
            return redirect()->route('siswa.pengajuan.index')
                ->with('error', $timeCheck['reason']);
        }

        return $next($request);
    }
}
