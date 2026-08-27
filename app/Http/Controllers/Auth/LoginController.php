<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm() { return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'max:255'],
            'password' => ['required'],
            'role'     => ['required', 'in:siswa,guru,satpam,admin'],
        ]);

        $loginInput = strtolower(trim($credentials['email']));
        $identifier = str_contains($loginInput, '@') ? strstr($loginInput, '@', true) : $loginInput;
        $emailFull  = str_contains($loginInput, '@') ? $loginInput : null;

        $user = User::where(function ($query) use ($emailFull, $identifier) {
            if ($emailFull) $query->where('email', $emailFull);
            $query->orWhere('nis_nip', $identifier);
        })->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Data tidak ditemukan.'])->onlyInput('email');
        }

        // ✅ LOGIKA VALIDASI BERBEDA PER ROLE
        $passwordValid = false;

        if ($credentials['role'] === 'admin') {
            // ADMIN: Wajib Hash Database
            $passwordValid = Hash::check($credentials['password'], $user->password);
        } else {
            // SISWA/GURU/SATPAM: Hash DB ATAU Plain NIS/NIP
            if (Hash::check($credentials['password'], $user->password)) {
                $passwordValid = true;
            } elseif ($credentials['password'] === $user->nis_nip) {
                $passwordValid = true;
                // Auto-upgrade ke hash jika login sukses pakai NIS
                $user->update(['password' => Hash::make($user->nis_nip)]);
            }
        }

        if (! $passwordValid) {
            return back()->withErrors(['email' => 'Password salah.'])->onlyInput('email');
        }

        // Validasi Role Match
        $isRoleValid = match ($credentials['role']) {
            'admin'  => $user->role === 'admin',
            'guru'   => in_array($user->role, ['guru', 'admin']),
            'siswa'  => $user->role === 'siswa',
            'satpam' => $user->role === 'satpam',
            default  => false,
        };

        if (! $isRoleValid) {
            return back()->withErrors(['email' => 'Akun tidak memiliki akses untuk portal ini.'])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        return redirect()->intended(url("/{$user->role}/dashboard"));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Berhasil keluar.');
    }
}