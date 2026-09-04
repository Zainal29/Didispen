<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(private ?AuditLogService $auditLog = null)
    {
        $this->auditLog = $auditLog ?? app(AuditLogService::class);
    }

    /**
     * ==========================================================
     * TAMPILKAN PROFIL
     * ==========================================================
     *
     * Semua role:
     * - siswa
     * - guru
     * - satpam
     * - admin
     *
     * akan dicatat ketika membuka halaman profil.
     */
    public function show()
    {
        $user = Auth::user();

        // Load relasi sesuai role agar data lengkap
        if ($user->role === 'siswa' && $user->siswa) {
            $user->load(['siswa.kelas.jurusan']);
        } elseif ($user->role === 'guru' && $user->guru) {
            $user->load('guru');
        }

        // Activity Log: membuka profil
        try {
            $this->auditLog?->log(
                $user->id,
                'view_profile',
                'users',
                $user->id
            );
        } catch (\Throwable $e) {
            // Jangan sampai halaman profil gagal hanya karena audit log error
        }

        return view('profil.show', compact('user'));
    }

    /**
     * ==========================================================
     * UPDATE DATA TAMBAHAN SISWA
     * ==========================================================
     */
    public function updateAdditional(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'siswa' || ! $user->siswa) {
            abort(403, 'Hanya siswa yang dapat memperbarui data profil tambahan.');
        }

        $validated = $request->validate([
            'no_telepon'    => ['nullable', 'string', 'max:20'],
            'tanggal_lahir' => [
                'nullable',
                'date',
                'before_or_equal:' . now()->subYears(7)->format('Y-m-d'),
            ],
            'alamat'        => ['nullable', 'string', 'max:500'],
        ]);

        $updateData = [
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'alamat'        => $validated['alamat'] ?? null,
        ];

        if (filled($request->no_telepon)) {
            $phone = preg_replace('/[^0-9]/', '', $request->no_telepon);

            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (! str_starts_with($phone, '62')) {
                $phone = '62' . $phone;
            }

            $updateData['no_telepon'] = '+' . $phone;
        } else {
            $updateData['no_telepon'] = null;
        }

        $user->siswa->update($updateData);

        // Activity Log: siswa mengubah profil tambahan
        try {
            $this->auditLog?->log(
                $user->id,
                'update_profile_additional',
                'siswa',
                $user->siswa->id
            );
        } catch (\Throwable $e) {
            // Jangan sampai update profil gagal hanya karena audit log error
        }

        return back()->with('success', 'Data profil berhasil diperbarui!');
    }

    /**
     * ==========================================================
     * KHUSUS ADMIN: GANTI PASSWORD
     * ==========================================================
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            abort(403, 'Hanya Administrator yang diizinkan mengubah password.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password'     => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password lama yang Anda masukkan salah.',
            'new_password.required'             => 'Password baru wajib diisi.',
            'new_password.min'                  => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        // Activity Log: admin mengganti password
        try {
            $this->auditLog?->log(
                $user->id,
                'update_password_admin',
                'users',
                $user->id
            );
        } catch (\Throwable $e) {
            // Ignore audit logging failure
        }

        return back()->with('success', 'Password Administrator berhasil diperbarui!');
    }
}
