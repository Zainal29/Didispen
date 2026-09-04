<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

    public function show()
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        // Load relasi sesuai role
        if ($user->role === 'siswa' && $user->siswa) {
            $user->loadMissing([
                'siswa.kelas.jurusan',
            ]);
        } elseif ($user->role === 'guru' && $user->guru) {
            $user->loadMissing([
                'guru',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT LOGIN
        |--------------------------------------------------------------------------
        */
        $loginHistory = AuditLog::query()
            ->where('user_id', $user->id)
            ->where('action', 'login')
            ->latest('created_at')
            ->take(10)
            ->get();

        $latestLogin = $loginHistory->first();

        $previousLogin = $loginHistory->get(1);

        /*
        |--------------------------------------------------------------------------
        | CEK PERUBAHAN PERANGKAT
        |--------------------------------------------------------------------------
        */
        $deviceChanged = false;

        if ($latestLogin && $previousLogin) {
            $deviceChanged =
                (string) ($latestLogin->device_type ?? '') !==
                    (string) ($previousLogin->device_type ?? '')
                ||
                (string) ($latestLogin->os ?? '') !==
                    (string) ($previousLogin->os ?? '')
                ||
                (string) ($latestLogin->browser ?? '') !==
                    (string) ($previousLogin->browser ?? '');
        }

        /*
        |--------------------------------------------------------------------------
        | LOG AKTIVITAS PROFIL
        |--------------------------------------------------------------------------
        */
        try {
            $this->auditLog?->log(
                $user->id,
                'view_profile',
                'users',
                $user->id
            );
        } catch (\Throwable $e) {
            // Jangan sampai audit error membuat halaman profil gagal
        }

        return view('profil.show', [
            'user'           => $user,
            'loginHistory'   => $loginHistory,
            'latestLogin'    => $latestLogin,
            'previousLogin'  => $previousLogin,
            'deviceChanged'  => $deviceChanged,
        ]);
    }

    public function updateAdditional(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        if ($user->role !== 'siswa' || ! $user->siswa) {
            abort(
                403,
                'Hanya siswa yang dapat memperbarui data profil tambahan.'
            );
        }

        $validated = $request->validate([
            'no_telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'tanggal_lahir' => [
                'nullable',
                'date',
                'before_or_equal:' . now()
                    ->subYears(7)
                    ->format('Y-m-d'),
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'no_telepon.max' =>
                'Nomor telepon maksimal 20 karakter.',

            'tanggal_lahir.date' =>
                'Format tanggal lahir tidak valid.',

            'tanggal_lahir.before_or_equal' =>
                'Tanggal lahir tidak valid.',

            'alamat.max' =>
                'Alamat maksimal 500 karakter.',
        ]);

        $updateData = [
            'tanggal_lahir' =>
                $validated['tanggal_lahir'] ?? null,

            'alamat' =>
                $validated['alamat'] ?? null,
        ];

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI NOMOR TELEPON
        |--------------------------------------------------------------------------
        */
        if (filled($validated['no_telepon'] ?? null)) {
            $phone = preg_replace(
                '/[^0-9]/',
                '',
                $validated['no_telepon']
            );

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

        try {
            $this->auditLog?->log(
                $user->id,
                'update_profile_additional',
                'siswa',
                $user->siswa->id
            );
        } catch (\Throwable $e) {
            // Abaikan jika audit gagal
        }

        return back()->with(
            'success',
            'Data profil berhasil diperbarui!'
        );
    }

    /**
     * KHUSUS ADMIN: Ganti Password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        if ($user->role !== 'admin') {
            abort(
                403,
                'Hanya Administrator yang diizinkan mengubah password.'
            );
        }

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'current_password.required' =>
                'Password saat ini wajib diisi.',

            'current_password.current_password' =>
                'Password lama yang Anda masukkan salah.',

            'new_password.required' =>
                'Password baru wajib diisi.',

            'new_password.min' =>
                'Password baru minimal 8 karakter.',

            'new_password.confirmed' =>
                'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make(
                $validated['new_password']
            ),
        ]);

        try {
            $this->auditLog?->log(
                $user->id,
                'update_password_admin',
                'users',
                $user->id
            );
        } catch (\Throwable $e) {
            // Abaikan jika audit gagal
        }

        return back()->with(
            'success',
            'Password Administrator berhasil diperbarui!'
        );
    }
}
