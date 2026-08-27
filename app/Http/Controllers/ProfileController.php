<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // Load relasi sesuai role agar data lengkap
        if ($user->role === 'siswa' && $user->siswa) {
            $user->load(['siswa.kelas.jurusan']);
        } elseif ($user->role === 'guru' && $user->guru) {
            $user->load('guru');
        }

        return view('profil.show', compact('user'));
    }

    public function updateAdditional(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'siswa' || ! $user->siswa) abort(403);

        $validated = $request->validate([
            'no_telepon'  => ['nullable', 'string', 'max:20'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(7)->format('Y-m-d')],
            'alamat'      => ['nullable', 'string', 'max:500'],
        ]);

        $updateData = [
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat'        => $validated['alamat'] ?? null,
        ];

        if (filled($request->no_telepon)) {
            $phone = preg_replace('/[^0-9]/', '', $request->no_telepon);
            if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
            elseif (! str_starts_with($phone, '62')) $phone = '62' . $phone;
            $updateData['no_telepon'] = '+' . $phone;
        }

        $user->siswa->update($updateData);
        return back()->with('success', 'Data profil berhasil diperbarui!');
    }

    /**
     * ✅ KHUSUS ADMIN: Ganti Password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') abort(403);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password lama salah.',
            'new_password.min'                  => 'Minimal 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update(['password' => Hash::make($validated['new_password'])]);
        return back()->with('success', 'Password Administrator berhasil diperbarui!');
    }
}