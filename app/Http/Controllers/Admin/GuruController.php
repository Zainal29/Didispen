<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruRequest;
use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index(Request $request)
    {
        $sortable = [
            'nip'            => 'NIP',
            'nama_lengkap'   => 'Nama',
            'mata_pelajaran' => 'Mata Pelajaran',
            'created_at'     => 'Terdaftar',
        ];

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        if (! array_key_exists($sort, $sortable)) $sort = 'created_at';
        if (! in_array($dir, ['asc', 'desc'])) $dir = 'desc';

        $query = Guru::with('user');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('nip', 'like', "%{$s}%")
                  ->orWhere('mata_pelajaran', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$s}%"));
            });
        }

        $gurus = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();

        return view('admin.guru.index', compact('gurus', 'sortable', 'sort', 'dir'));
    }

    public function store(StoreGuruRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'role'     => 'guru',
                'nis_nip'  => $request->nip,
            ]);

            Guru::create([
                'user_id'        => $user->id,
                'nip'            => $request->nip,
                'nama_lengkap'   => $request->nama_lengkap,
                'mata_pelajaran' => $request->mata_pelajaran,
            ]);

            $this->auditLog->log(auth()->id(), 'create_guru', 'guru', $user->id);
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil ditambahkan. Password akun dikelola oleh SiPintu/Sijuna.');
    }

    public function update(StoreGuruRequest $request, Guru $guru)
    {
        DB::transaction(function () use ($request, $guru) {
            $guru->user->update([
                'name'    => $request->name,
                'email'   => $request->email,
                'nis_nip' => $request->nip,
            ]);

            $guru->update($request->only(['nip', 'nama_lengkap', 'mata_pelajaran']));
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->user->delete();
        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru berhasil dihapus.');
    }

    public function checklog()
    {
        $logs = GuruChecklog::with('guru.user')
            ->orderByRaw("FIELD(status, 'keluar', 'selesai')")
            ->orderBy('jam_keluar', 'desc')
            ->paginate(15);

        return view('admin.guru.checklog', compact('logs'));
    }
}
