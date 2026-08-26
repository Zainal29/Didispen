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
     * Tampilkan halaman login
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
        // 1. Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required'],
            'role' => ['required', 'in:siswa,guru,satpam,admin'],
        ]);

        $loginInput = strtolower(trim($credentials['email']));

        // Tentukan identifier (NIS/NIP/ID) dan Email
        // Logika: Jika ada '@', anggap itu email. Jika tidak, anggap itu NIS/NIP.
        $identifier = str_contains($loginInput, '@') ? strstr($loginInput, '@', true) : $loginInput;
        $emailFull = str_contains($loginInput, '@') ? $loginInput : null;

        // 2. Cari user berdasarkan email ATAU nis_nip
        $user = User::where(function ($query) use ($emailFull, $identifier) {
            if ($emailFull) {
                $query->where('email', $emailFull);
            }
            // Selalu cek nis_nip karena identifier pasti berisi NIS/NIP
            $query->orWhere('nis_nip', $identifier);
        })->first();

        // 3. Cek apakah user ditemukan
        if (! $user) {
            return back()->withErrors([
                'email' => 'Data tidak ditemukan. Pastikan NIS/NIP/ID atau Email sudah benar.',
            ])->onlyInput('email');
        }

        // 4. Verifikasi Password (Logika SiPintu)
        $passwordValid = false;
        $inputPassword = $credentials['password'];

        // A. Cek menggunakan Hash Laravel (Standar)
        if (Hash::check($inputPassword, $user->password)) {
            $passwordValid = true;
        }
        // B. Fallback: Cek Password Default SiPintu (Biasanya sama dengan NIS/NIP)
        // Karena form mengirim password = identifier secara otomatis
        elseif ($inputPassword === $user->nis_nip) {
            $passwordValid = true;

            // FITUR TAMBAHAN: Auto-Upgrade Password
            // Jika login sukses pakai default, kita encrypt passwordnya di database
            // supaya lain kali proses login lebih cepat dan standar.
            $user->update(['password' => Hash::make($user->nis_nip)]);
        }

        if (! $passwordValid) {
            return back()->withErrors([
                'email' => 'Password salah. Gunakan NIS/NIP/ID Anda sebagai password.',
            ])->onlyInput('email');
        }

        // 5. Validasi Role (Mencegah siswa masuk lewat tab Guru, dll)
        $requestedRole = $credentials['role'];
        $userRole = $user->role;

        $isRoleAllowed = match ($requestedRole) {
            'admin' => $userRole === 'admin',
            'guru' => in_array($userRole, ['guru', 'admin']),
            'siswa' => $userRole === 'siswa',
            'satpam' => $userRole === 'satpam',
            default => false,
        };

        if (! $isRoleAllowed) {
            return back()->withErrors([
                'email' => 'Akun ini tidak terdaftar sebagai '.ucfirst($requestedRole).'.',
            ])->onlyInput('email');
        }

        // 6. Proses Login Session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 7. Redirect ke Dashboard sesuai role
        return redirect()->intended(url("/{$userRole}/dashboard"));
    }

    /**
     * Proses Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
