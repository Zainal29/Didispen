<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GuruPiketMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user?->guru?->piketHariIni()) {
            abort(403, 'Anda tidak memiliki jadwal piket hari ini.');
        }
        return $next($request);
    }
}
