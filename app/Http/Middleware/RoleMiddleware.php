<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
        }

        $isAllowed = false;
        foreach ($roles as $role) {
            if ($role === 'superadmin' && $user->isSuperAdmin()) {
                $isAllowed = true;
                break;
            } elseif ($role === 'admin' && $user->isAdmin()) {
                $isAllowed = true;
                break;
            } elseif (($user->role === $role) || ($user->isSuperAdmin() && in_array($role, ['siswa', 'admin'], true))) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
        }

        // Pastikan akun siswa yang sedang memiliki sesi tetap dicek keaktifannya
        if ($user->role === 'siswa' && $user->siswa && ! $user->siswa->status_aktif) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun siswa Anda tidak aktif. Silakan hubungi Administrator.',
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun siswa Anda tidak aktif. Silakan hubungi Administrator.',
            ]);
        }

        // Pastikan akun guru yang sedang memiliki sesi tetap dicek keaktifannya
        if ($user->role === 'guru' && $user->guru && ! $user->guru->status_aktif) {
            \Illuminate\Support\Facades\Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun guru Anda tidak aktif. Silakan hubungi Administrator.',
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun guru Anda tidak aktif. Silakan hubungi Administrator.',
            ]);
        }

        return $next($request);
    }
}
