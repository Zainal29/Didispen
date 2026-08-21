<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi input (Admin tidak ada di sini karena form mengirim 'guru' untuk admin)
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required'],
            'role' => ['required', 'in:siswa,guru,satpam'], 
        ]);

        $loginInput = strtolower(trim($credentials['email']));
        
        // Auto-append domain jika user hanya mengetik NIS/NIP atau nama depan
        if (!str_contains($loginInput, '@')) {
            $loginInput .= '@smkn1bangsri.sch.id';
        }

        $identifier = strstr($loginInput, '@', true) ?: $loginInput;

        // 2. Cari user berdasarkan email atau nis_nip (TANPA memfilter role di awal)
        $user = User::where(function ($query) use ($loginInput, $identifier) {
            $query->where('email', $loginInput)
                  ->orWhere('nis_nip', $identifier);
        })->first();

        // 3. Cek apakah user ditemukan dan password cocok
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Email, NIS/NIP, atau password yang Anda masukkan salah.'
            ])->onlyInput('email');
        }

        // 4. VALIDASI ROLE BERDASARKAN TAB YANG DIPILIH DI FORM
        $requestedRole = $credentials['role'];
        $isRoleValid = false;

        if ($requestedRole === 'guru') {
            // Jika form mengirim 'guru', izinkan role 'guru' ATAU 'admin'
            if (in_array($user->role, ['guru', 'admin'])) {
                $isRoleValid = true;
            }
        } elseif ($requestedRole === 'siswa' && $user->role === 'siswa') {
            $isRoleValid = true;
        } elseif ($requestedRole === 'satpam' && $user->role === 'satpam') {
            $isRoleValid = true;
        }

        // 5. Tolak jika role user tidak diizinkan masuk melalui tab tersebut
        if (!$isRoleValid) {
            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses untuk masuk melalui pintu tersebut.'
            ])->onlyInput('email');
        }

        // 6. Jika semua lolos, lakukan login
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 7. Redirect berdasarkan role ASLI user di database
        $actualRole = $user->role;
        
        return redirect()->intended(url("/{$actualRole}/dashboard"));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect(url('/login'));
    }
}