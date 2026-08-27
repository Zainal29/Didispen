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
            'email' => [
                'required',
                'string',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],

            'role' => [
                'required',
                'in:siswa,guru,satpam,admin',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI INPUT
        |--------------------------------------------------------------------------
        */

        $loginInput = strtolower(
            trim($credentials['email'])
        );


        /*
        |--------------------------------------------------------------------------
        | CARI IDENTITAS
        |--------------------------------------------------------------------------
        |
        | Bisa menggunakan:
        | - email
        | - NIS
        | - NIP
        |
        */

        $identifier = str_contains($loginInput, '@')
            ? strstr($loginInput, '@', true)
            : $loginInput;


        $emailFull = str_contains($loginInput, '@')
            ? $loginInput
            : null;


        $user = User::where(function ($query) use (
            $emailFull,
            $identifier
        ) {

            if ($emailFull) {

                $query->where(
                    'email',
                    $emailFull
                );

            }

            $query->orWhere(
                'nis_nip',
                $identifier
            );

        })->first();


        /*
        |--------------------------------------------------------------------------
        | USER TIDAK DITEMUKAN
        |--------------------------------------------------------------------------
        */

        if (!$user) {

            return back()
                ->withErrors([
                    'email' => 'Data akun tidak ditemukan.'
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI ROLE
        |--------------------------------------------------------------------------
        |
        | ADMIN ADALAH PENGECUALIAN.
        |
        | Admin boleh masuk melalui portal Guru Piket.
        |
        */

        $isRoleValid = match ($credentials['role']) {

            /*
             * SISWA
             */
            'siswa' =>
                $user->role === 'siswa',


            /*
             * GURU
             *
             * ADMIN JUGA BOLEH MASUK DI SINI.
             */
            'guru' =>
                in_array(
                    $user->role,
                    ['guru', 'admin'],
                    true
                ),


            /*
             * SATPAM
             */
            'satpam' =>
                $user->role === 'satpam',


            /*
             * ADMIN
             */
            'admin' =>
                $user->role === 'admin',


            default => false,
        };


        /*
        |--------------------------------------------------------------------------
        | ROLE TIDAK SESUAI
        |--------------------------------------------------------------------------
        */

        if (!$isRoleValid) {

            return back()
                ->withErrors([
                    'email' =>
                        'Akun tidak memiliki akses untuk portal ini.'
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI PASSWORD
        |--------------------------------------------------------------------------
        */

        $passwordValid = false;


        /*
        |--------------------------------------------------------------------------
        | SISWA
        |--------------------------------------------------------------------------
        |
        | Siswa boleh menggunakan:
        |
        | password hash database
        | ATAU
        | NIS sebagai password awal
        |
        */

        if ($user->role === 'siswa') {

            /*
             * Coba password hash terlebih dahulu.
             */
            if (
                !empty($user->password) &&
                Hash::check(
                    $credentials['password'],
                    $user->password
                )
            ) {

                $passwordValid = true;

            }


            /*
             * Jika belum berhasil,
             * izinkan password = NIS.
             */
            elseif (
                !empty($user->nis_nip) &&
                $credentials['password'] === $user->nis_nip
            ) {

                $passwordValid = true;


                /*
                 * Upgrade password menjadi hash.
                 */
                $user->update([
                    'password' => Hash::make(
                        $user->nis_nip
                    ),
                ]);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | GURU
        |--------------------------------------------------------------------------
        |
        | Password GURU sekarang 100% MANUAL.
        |
        | Tidak lagi:
        | - mengambil password otomatis
        | - menggunakan NIP otomatis
        | - mengambil password dari SIPINTU
        |
        */

        elseif ($user->role === 'guru') {

            if (
                !empty($user->password) &&
                Hash::check(
                    $credentials['password'],
                    $user->password
                )
            ) {

                $passwordValid = true;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        |
        | Admin wajib menggunakan password yang ada
        | di database dalam bentuk hash.
        |
        */

        elseif ($user->role === 'admin') {

            if (
                !empty($user->password) &&
                Hash::check(
                    $credentials['password'],
                    $user->password
                )
            ) {

                $passwordValid = true;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SATPAM
        |--------------------------------------------------------------------------
        |
        | Satpam juga menggunakan password manual.
        |
        */

        elseif ($user->role === 'satpam') {

            if (
                !empty($user->password) &&
                Hash::check(
                    $credentials['password'],
                    $user->password
                )
            ) {

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
                ->withErrors([
                    'email' =>
                        'Password yang dimasukkan salah.'
                ])
                ->withInput();

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */

        Auth::login(
            $user,
            $request->boolean('remember')
        );


        /*
        |--------------------------------------------------------------------------
        | REGENERATE SESSION
        |--------------------------------------------------------------------------
        */

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        |
        | Admin tetap diarahkan ke:
        | /admin/dashboard
        |
        | walaupun login melalui tab Guru Piket.
        |
        */

        return redirect()->intended(
            url("/{$user->role}/dashboard")
        );
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
            ->with(
                'success',
                'Berhasil keluar.'
            );
    }
}
