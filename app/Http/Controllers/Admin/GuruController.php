<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruRequest;
use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index()
    {
        $gurus = Guru::with('user')->latest()->paginate(15);
        return view('admin.guru.index', compact('gurus'));
    }

    public function store(StoreGuruRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password ?? 'password'),
                'role' => 'guru',
                'nis_nip' => $request->nip,
            ]);
            Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'mata_pelajaran' => $request->mata_pelajaran,
            ]);
            $this->auditLog->log(auth()->id(), 'create_guru', 'guru', $user->id);
        });
        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function update(StoreGuruRequest $request, Guru $guru)
    {
        DB::transaction(function () use ($request, $guru) {
            $guru->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'nis_nip' => $request->nip,
            ]);
            if ($request->filled('password')) {
                $guru->user->update(['password' => Hash::make($request->password)]);
            }
            $guru->update($request->only(['nip', 'nama_lengkap', 'mata_pelajaran']));
        });
        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->user->delete();
        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil dihapus.');
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
