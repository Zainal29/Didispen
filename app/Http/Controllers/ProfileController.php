<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil siswa
     */
    public function show()
    {
        $user = Auth::user();

        // Pastikan user adalah siswa dan load relasi yang diperlukan
        if ($user->role !== 'siswa' || ! $user->siswa) {
            abort(403, 'Akses ditolak.');
        }

        $user->load(['siswa.kelas.jurusan']);

        return view('profil.show', compact('user'));
    }

    /**
     * Update No. Telepon / WhatsApp Siswa
     */
    public function updatePhone(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'siswa' || ! $user->siswa) {
            return back()->with('error', 'Hanya siswa yang dapat mengubah no. telepon.');
        }

        $validated = $request->validate([
            'no_telepon' => ['required', 'string', 'max:20'],
        ], [
            'no_telepon.required' => 'No. telepon / WhatsApp wajib diisi.',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['no_telepon']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (! str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        $phone = '+' . $phone;

        $user->siswa->update(['no_telepon' => $phone]);

        return back()->with('success', 'No. telepon / WhatsApp berhasil diperbarui!');
    }

    /**
     * Update data tambahan (Alamat & Tanggal Lahir)
     */
    public function updateAdditional(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'siswa' || ! $user->siswa) {
            return back()->with('error', 'Hanya siswa yang dapat mengubah data ini.');
        }

        $validated = $request->validate([
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:'.now()->subYears(7)->format('Y-m-d')],
            'alamat' => ['nullable', 'string', 'max:500'],
        ], [
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak valid (usia minimal 7 tahun).',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
        ]);

        $updateData = [
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
        ];

        if (filled($request->no_telepon)) {
            $phone = preg_replace('/[^0-9]/', '', $request->no_telepon);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (! str_starts_with($phone, '62')) {
                $phone = '62' . $phone;
            }
            $updateData['no_telepon'] = '+' . $phone;
        }

        $user->siswa->update($updateData);

        return back()->with('success', 'Data profil berhasil diperbarui!');
    }
}
