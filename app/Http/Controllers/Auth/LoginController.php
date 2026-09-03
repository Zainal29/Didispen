<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Halaman login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:siswa,guru,satpam,admin'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI INPUT
        |--------------------------------------------------------------------------
        */
        $loginInput = strtolower(trim($credentials['email']));

        /*
        |--------------------------------------------------------------------------
        | CARI IDENTITAS
        |--------------------------------------------------------------------------
        */
        $identifier = str_contains($loginInput, '@')
            ? strstr($loginInput, '@', true)
            : $loginInput;

        $emailFull = str_contains($loginInput, '@')
            ? $loginInput
            : null;

        $user = User::where(function ($query) use ($emailFull, $identifier) {
            if ($emailFull) {
                $query->where('email', $emailFull);
            }
            $query->orWhere('nis_nip', $identifier);
        })->first();

        /*
        |--------------------------------------------------------------------------
        | USER TIDAK DITEMUKAN
        |--------------------------------------------------------------------------
        */
        if (!$user) {
            return back()
                ->withErrors(['email' => 'Data akun tidak ditemukan.'])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI ROLE
        |--------------------------------------------------------------------------
        */
        $isRoleValid = match ($credentials['role']) {
            'siswa' => $user->role === 'siswa',
            'guru' => in_array($user->role, ['guru', 'admin'], true),
            'satpam' => $user->role === 'satpam',
            'admin' => $user->role === 'admin',
            default => false,
        };

        if (!$isRoleValid) {
            return back()
                ->withErrors(['email' => 'Akun tidak memiliki akses untuk portal ini.'])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI STATUS AKTIF (SISWA / GURU)
        |--------------------------------------------------------------------------
        */
        if ($user->role === 'siswa') {
            if (!$user->siswa || !$user->siswa->status_aktif) {
                return back()
                    ->withErrors(['email' => 'Akun siswa tidak aktif atau terdaftar sebagai alumni.'])
                    ->withInput();
            }
        } elseif ($user->role === 'guru') {
            if ($user->guru && !$user->guru->status_aktif) {
                return back()
                    ->withErrors(['email' => 'Akun guru tidak aktif.'])
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI PASSWORD
        |--------------------------------------------------------------------------
        */
        $passwordValid = false;

        if ($user->role === 'siswa') {
            if (!empty($user->password) && Hash::check($credentials['password'], $user->password)) {
                $passwordValid = true;
            }
        } elseif ($user->role === 'guru') {
            if (!empty($user->password) && Hash::check($credentials['password'], $user->password)) {
                $passwordValid = true;
            }
        } elseif ($user->role === 'admin') {
            if (!empty($user->password) && Hash::check($credentials['password'], $user->password)) {
                $passwordValid = true;
            }
        } elseif ($user->role === 'satpam') {
            if (!empty($user->password) && Hash::check($credentials['password'], $user->password)) {
                $passwordValid = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PASSWORD SALAH
        |--------------------------------------------------------------------------
        */
        if (!$passwordValid) {
            return back()
                ->withErrors(['email' => 'Password yang dimasukkan salah.'])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(url("/{$user->role}/dashboard"));
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Berhasil keluar.');
    }
}
