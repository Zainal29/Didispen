<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GuruPiketMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Skema baru: setiap guru yang login otomatis dianggap bertugas.
        return $next($request);
    }
}
