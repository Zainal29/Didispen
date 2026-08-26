<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    /**
     * ✅ REVISI SIPINTU: Middleware ini TIDAK digunakan lagi untuk redirect.
     * Field `must_change_password` tetap ada di database tetapi DIABAIKAN,
     * karena password dikelola sepenuhnya oleh SiPintu/Sijuna.
     * Middleware hanya meneruskan request tanpa interupsi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
