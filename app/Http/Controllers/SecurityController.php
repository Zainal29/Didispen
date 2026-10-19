<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class SecurityController extends Controller
{
    public function showPrompt()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        return redirect()->to("/{$user->role}/dashboard");
    }

    /**
     * Update Nomor WhatsApp (dan opsional ganti password jika diisi)
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        $rules = [
            'no_wa' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
        ];

        // Jika user mengisi field password baru
        if ($request->filled('password')) {
            $rules['password'] = [
                'required', 
                'confirmed', 
                Password::min(8)->letters()->numbers(),
                function ($attribute, $value, $fail) {
                    if (strtolower((string) $value) === 'password') {
                        $fail('Password baru tidak boleh menggunakan kata sandi default ("password").');
                    }
                },
            ];
        }

        $messages = [
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Format nomor WhatsApp tidak valid (contoh: 081234567890).',
            'password.required' => 'Password baru wajib diisi jika ingin mengubah kata sandi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password minimal 8 karakter dengan kombinasi huruf dan angka.',
        ];

        $validated = $request->validate($rules, $messages);

        // Format nomor WA jadi standar internasional (+628xxx)
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['no_wa']);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        } elseif (! str_starts_with($cleanPhone, '62')) {
            $cleanPhone = '62' . $cleanPhone;
        }
        $formattedPhone = '+' . $cleanPhone;

        $dataToUpdate = [
            'no_wa' => $formattedPhone,
        ];

        if (! empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
            $dataToUpdate['password_changed_at'] = now();
        }

        $user->update($dataToUpdate);

        // Sinkronkan ke data profil siswa / guru jika ada relasinya
        if ($user->isSiswa() && $user->siswa) {
            $user->siswa->update(['no_telepon' => $formattedPhone]);
        } elseif ($user->isGuru() && $user->guru) {
            $user->guru->update(['no_telepon' => $formattedPhone]);
        }

        $msg = 'Nomor WhatsApp berhasil disimpan!';
        if (! empty($validated['password'])) {
            $msg = 'Nomor WhatsApp dan kata sandi berhasil diperbarui!';
        }

        return back()->with('success', $msg);
    }

    /**
     * Pengingat ganti password di SiPintu
     */
    public function checkSipintuStatus(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->isDefaultPassword()) {
            return back()->with('success', 'Password Anda sudah aman dan bukan password default.');
        }

        return back()->with('info', 'Silakan kunjungi portal SiPintu untuk mengganti kata sandi bawaan demi keamanan akun Anda.');
    }
}
