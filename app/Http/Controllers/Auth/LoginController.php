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
     *
     * Login dapat menggunakan:
     * - NIP
     * - Email sekolah (NIP@smkn1bangsri.sch.id)
     * - Email asli guru dari SiPintu
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:siswa,guru,satpam,admin'],
        ]);

        $loginInput = strtolower(trim($credentials['email']));

        // Normalisasi kemungkinan typo domain seperti @smkn!bangsri.sch.id -> @smkn1bangsri.sch.id
        $loginInput = str_replace('@smkn!bangsri.sch.id', '@smkn1bangsri.sch.id', $loginInput);

        // Ekstraksi NIP / NIS jika login menggunakan format email sekolah atau sijuna
        $nipFromSchoolEmail = null;
        if (str_ends_with($loginInput, '@smkn1bangsri.sch.id') || str_ends_with($loginInput, '@sijuna.com')) {
            $nipFromSchoolEmail = strstr($loginInput, '@', true) ?: null;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. CARI USER
        |--------------------------------------------------------------------------
        |
        | Untuk login:
        | - NIP                       -> users.nis_nip
        | - Email akun                -> users.email
        | - Email asli Guru (Google)  -> guru.email
        | - Email sekolah (NIP@smkn1bangsri.sch.id / NIP@smkn!bangsri.sch.id)
        |
        */
        $userQuery = User::where(function ($query) use ($loginInput, $nipFromSchoolEmail) {
            // Email akun (bisa email sekolah atau email terdaftar)
            $query->where('email', $loginInput)
                // NIP / NIS
                ->orWhere('nis_nip', $loginInput)
                // Email asli guru dari SiPintu (Google / akun lain)
                ->orWhereHas('guru', function ($guruQuery) use ($loginInput) {
                    $guruQuery->where('email', $loginInput);
                });

            // Jika input berupa email sekolah (NIP@smkn1bangsri.sch.id), cek juga berdasarkan NIP langsung
            if ($nipFromSchoolEmail) {
                $query->orWhere('nis_nip', $nipFromSchoolEmail)
                    ->orWhereHas('guru', function ($guruQuery) use ($nipFromSchoolEmail) {
                        $guruQuery->where('nip', $nipFromSchoolEmail);
                    });
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 2. FILTER ROLE
        |--------------------------------------------------------------------------
        */
        $userQuery->where(function ($query) use ($credentials) {
            match ($credentials['role']) {
                'siswa' => $query->where('role', 'siswa'),

                'guru' => $query->whereIn('role', ['guru', 'admin']),

                'satpam' => $query->where('role', 'satpam'),

                'admin' => $query->where('role', 'admin'),
            };
        });

        $user = $userQuery->first();

        /*
        |--------------------------------------------------------------------------
        | 3. USER TIDAK DITEMUKAN
        |--------------------------------------------------------------------------
        */
        if (! $user) {
            return back()
                ->withErrors([
                    'email' => 'NIS/Email atau password salah.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 4. CEK LOCKOUT
        |--------------------------------------------------------------------------
        */
        if ($user->locked_until && $user->locked_until->isFuture()) {
            $minutesRemaining = $user->locked_until->diffInMinutes(now());

            $this->auditLog->log(
                $user->id,
                'login_attempt_locked',
                'users',
                $user->id,
                null,
                [
                    'reason' => "Account locked, {$minutesRemaining} mins remaining",
                ]
            );

            return back()->withErrors([
                'email' => "Akun terkunci sementara karena terlalu banyak percobaan gagal. Silakan coba lagi dalam {$minutesRemaining} menit atau hubungi Admin.",
            ])->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 5. VALIDASI ROLE
        |--------------------------------------------------------------------------
        */
        $isRoleValid = match ($credentials['role']) {
            'siswa' => $user->role === 'siswa',

            'guru' => in_array(
                $user->role,
                ['guru', 'admin'],
                true
            ),

            'satpam' => $user->role === 'satpam',

            'admin' => $user->role === 'admin',

            default => false,
        };

        if (! $isRoleValid) {
            return back()
                ->withErrors([
                    'email' => 'NIS/Email atau password salah.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 6. VALIDASI STATUS SISWA
        |--------------------------------------------------------------------------
        */
        if (
            $user->role === 'siswa'
            && (
                ! $user->siswa
                || ! $user->siswa->status_aktif
            )
        ) {
            return back()
                ->withErrors([
                    'email' => 'NIS/Email atau password salah.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 7. VALIDASI STATUS GURU
        |--------------------------------------------------------------------------
        */
        if (
            $user->role === 'guru'
            && $user->guru
            && ! $user->guru->status_aktif
        ) {
            return back()
                ->withErrors([
                    'email' => 'NIS/Email atau password salah.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 8. CEK PASSWORD
        |--------------------------------------------------------------------------
        */
        if (! Hash::check(
            $credentials['password'],
            $user->password
        )) {
            $user->increment('failed_login_attempts');

            $currentAttempts = $user->fresh()->failed_login_attempts;

            /*
            | Lock setelah 5 kali gagal
            */
            if ($currentAttempts >= 5) {
                $user->update([
                    'locked_until' => now()->addMinutes(15),
                ]);

                $this->auditLog->log(
                    $user->id,
                    'login_failed_locked',
                    'users',
                    $user->id,
                    null,
                    [
                        'attempts' => $currentAttempts,
                        'locked_for' => '15 minutes',
                    ]
                );

                return back()->withErrors([
                    'email' => 'Akun terkunci sementara karena 5 kali percobaan gagal. Silakan coba lagi dalam 15 menit.',
                ])->withInput();
            }

            $this->auditLog->log(
                $user->id,
                'login_failed',
                'users',
                $user->id,
                null,
                [
                    'attempts' => $currentAttempts,
                ]
            );

            return back()
                ->withErrors([
                    'email' => 'NIS/Email atau password salah.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 9. LOGIN BERHASIL
        |--------------------------------------------------------------------------
        */
        if (
            $user->failed_login_attempts > 0
            || $user->locked_until
        ) {
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);
        }

        Auth::login(
            $user,
            $request->boolean('remember')
        );

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | 10. AUDIT LOG
        |--------------------------------------------------------------------------
        */
        $this->auditLog->log(
            $user->id,
            'login',
            'users',
            $user->id,
            null,
            [
                'role' => $user->role,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 11. REDIRECT
        |--------------------------------------------------------------------------
        */
        return redirect()->intended(
            url("/{$user->role}/dashboard")
        );
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

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

