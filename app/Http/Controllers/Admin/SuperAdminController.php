<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    /**
     * Tampilkan daftar akun dengan hak akses Super Admin.
     */
    public function index(Request $request)
    {
        $query = User::with(['siswa.kelas.jurusan', 'guru'])
            ->where('is_superadmin', true);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nis_nip', 'like', "%{$search}%")
                  ->orWhereHas('siswa', fn($sq) => $sq->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $superadmins = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        $totalSuperadmin = User::where('is_superadmin', true)->count();
        $totalSiswaSuperadmin = User::where('is_superadmin', true)->where('role', 'siswa')->count();
        $totalNonSiswaSuperadmin = User::where('is_superadmin', true)->where('role', '!=', 'siswa')->count();

        return view('admin.superadmin.index', compact(
            'superadmins',
            'totalSuperadmin',
            'totalSiswaSuperadmin',
            'totalNonSiswaSuperadmin'
        ));
    }

    /**
     * Tambahkan / jadikan pengguna sebagai Super Admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ], [
            'identifier.required' => 'NIS, Email, atau Siswa wajib dipilih/diisi.',
        ]);

        $identifier = trim((string) $request->identifier);

        $user = User::where('id', $identifier)
            ->orWhere('nis_nip', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user) {
            return back()->with('error', "Pengguna dengan NIS / Email '{$identifier}' tidak ditemukan.");
        }

        if ($user->isSuperAdmin()) {
            return back()->with('info', "Akun '{$user->name}' sudah berstatus Super Admin.");
        }

        $user->is_superadmin = true;
        $user->save();

        $this->auditLog->log(auth()->id(), 'grant_superadmin', 'users', $user->id);

        $roleText = $user->role === 'siswa' ? 'Siswa (Dual-Role Admin)' : ucfirst($user->role);
        return back()->with('success', "Berhasil! Akun '{$user->name}' [{$roleText}] sekarang resmi memiliki hak akses Super Admin.");
    }

    /**
     * Cabut hak akses Super Admin.
     */
    public function destroy(User $user)
    {
        // Cegah administrator mencabut dirinya sendiri jika merupakan satu-satunya superadmin
        if ($user->id === auth()->id() && User::where('is_superadmin', true)->count() <= 1) {
            return back()->with('error', 'Tidak dapat mencabut hak Super Admin akun Anda sendiri karena sistem membutuhkan minimal 1 akun Super Admin.');
        }

        $user->is_superadmin = false;
        $user->save();

        $this->auditLog->log(auth()->id(), 'revoke_superadmin', 'users', $user->id);

        return back()->with('success', "Hak akses Super Admin untuk '{$user->name}' berhasil dicabut.");
    }

    /**
     * Endpoint API untuk pencarian siswa kandidat Super Admin.
     */
    public function searchSiswa(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $siswas = Siswa::with(['user', 'kelas'])
            ->where('status_aktif', true)
            ->where(function ($query) use ($q) {
                $query->where('nama_lengkap', 'like', "%{$q}%")
                      ->orWhere('nis_nip', 'like', "%{$q}%")
                      ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$q}%"));
            })
            ->limit(15)
            ->get()
            ->map(fn($s) => [
                'id' => $s->user?->id ?? $s->id,
                'siswa_id' => $s->id,
                'nama' => $s->nama_lengkap,
                'nis' => $s->nis_nip ?? $s->user?->nis_nip ?? '-',
                'kelas' => $s->kelas?->nama_kelas ?? '-',
                'email' => $s->user?->email ?? '-',
                'is_superadmin' => (bool) ($s->user?->is_superadmin ?? false),
            ]);

        return response()->json($siswas);
    }
}
