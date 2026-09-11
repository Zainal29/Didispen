<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Halaman login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login (dengan Anti Brute-Force & Lockout).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:siswa,guru,satpam,admin'],
        ]);

        $loginInput = strtolower(trim($credentials['email']));
        $identifier = str_contains($loginInput, '@') ? strstr($loginInput, '@', true) : $loginInput;
        $emailFull = str_contains($loginInput, '@') ? $loginInput : null;

        $user = User::where(function ($query) use ($emailFull, $identifier) {
            if ($emailFull) {
                $query->where('email', $emailFull);
            }
            $query->orWhere('nis_nip', $identifier);
        })->first();

        /*
        |--------------------------------------------------------------------------
        | 1. PENGECEKAN USER & PESAN ERROR GENERIK (Mencegah User Enumeration)
        |--------------------------------------------------------------------------
        */
        if (! $user) {
            return back()->withErrors(['email' => 'NIS/Email atau password salah.'])->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 2. CEK STATUS LOCKOUT AKUN
        |--------------------------------------------------------------------------
        */
        if ($user->locked_until && $user->locked_until->isFuture()) {
            $minutesRemaining = $user->locked_until->diffInMinutes(now());

            $this->auditLog->log(
                $user->id, 'login_attempt_locked', 'users', $user->id, null,
                ['reason' => "Account locked, {$minutesRemaining} mins remaining"]
            );

            return back()->withErrors([
                'email' => "Akun terkunci sementara karena terlalu banyak percobaan gagal. Silakan coba lagi dalam {$minutesRemaining} menit atau hubungi Admin."
            ])->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 3. VALIDASI ROLE & STATUS (Dengan error generik jika gagal)
        |--------------------------------------------------------------------------
        */
        $isRoleValid = match ($credentials['role']) {
            'siswa' => $user->role === 'siswa',
            'guru' => in_array($user->role, ['guru', 'admin'], true),
            'satpam' => $user->role === 'satpam',
            'admin' => $user->role === 'admin',
            default => false,
        };

        if (! $isRoleValid) {
            return back()->withErrors(['email' => 'NIS/Email atau password salah.'])->withInput();
        }

        if ($user->role === 'siswa' && (! $user->siswa || ! $user->siswa->status_aktif)) {
            return back()->withErrors(['email' => 'NIS/Email atau password salah.'])->withInput();
        }

        if ($user->role === 'guru' && $user->guru && ! $user->guru->status_aktif) {
            return back()->withErrors(['email' => 'NIS/Email atau password salah.'])->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 4. VALIDASI PASSWORD & LOGIC BRUTE-FORCE
        |--------------------------------------------------------------------------
        */
        if (! Hash::check($credentials['password'], $user->password)) {
            // Password salah: Increment counter
            $user->increment('failed_login_attempts');
            $currentAttempts = $user->fresh()->failed_login_attempts;

            if ($currentAttempts >= 5) {
                // Kunci akun selama 15 menit
                $user->update(['locked_until' => now()->addMinutes(15)]);

                $this->auditLog->log(
                    $user->id, 'login_failed_locked', 'users', $user->id, null,
                    ['attempts' => $currentAttempts, 'locked_for' => '15 minutes']
                );

                return back()->withErrors([
                    'email' => 'Akun terkunci sementara karena 5 kali percobaan gagal. Silakan coba lagi dalam 15 menit.'
                ])->withInput();
            }

            $this->auditLog->log(
                $user->id, 'login_failed', 'users', $user->id, null,
                ['attempts' => $currentAttempts]
            );

            return back()->withErrors(['email' => 'NIS/Email atau password salah.'])->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 5. LOGIN BERHASIL: RESET COUNTER
        |--------------------------------------------------------------------------
        */
        if ($user->failed_login_attempts > 0 || $user->locked_until) {
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->auditLog->log($user->id, 'login', 'users', $user->id, null, ['role' => $user->role]);

        return redirect()->intended(url("/{$user->role}/dashboard"));
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | AUDIT LOG LOGOUT
        |--------------------------------------------------------------------------
        */
        if ($user) {
            $this->auditLog->log(
                $user->id,
                'logout',
                'users',
                $user->id,
                null,
                [
                    'role' => $user->role,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOGOUT
        |--------------------------------------------------------------------------
        */
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')
            ->with(
                'success',
                'Berhasil keluar.'
            );
    }
}
