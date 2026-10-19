<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecurityProfileUpdated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->needsSecurityUpdate()) {
            // Kecualikan route form update & logout agar tidak redirect loop (macet)
            $exemptRoutes = [
                'security.prompt',
                'security.update',
                'logout',
            ];

            if (!$request->routeIs($exemptRoutes)) {
                return redirect()->route('security.prompt')
                    ->with('warning', 'Demi keamanan, Anda wajib mengganti password default dan memasukkan nomor WhatsApp aktif.');
            }
        }

        return $next($request);
    }
}

