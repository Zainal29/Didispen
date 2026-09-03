<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiswaRequest;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index(Request $request)
    {
        $sortable = [
            'nis_nip'      => 'NIS',
            'nama_lengkap' => 'Nama',
            'kelas_id'     => 'Kelas',
            'jurusan_id'   => 'Jurusan',
            'created_at'   => 'Terdaftar',
        ];

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        if (! array_key_exists($sort, $sortable)) $sort = 'created_at';
        if (! in_array($dir, ['asc', 'desc'])) $dir = 'desc';

        $query = Siswa::with(['user', 'kelas', 'jurusan'])->where('status_aktif', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('nis_nip', 'like', "%{$s}%")
                  ->orWhereHas('kelas', fn ($k) => $k->where('nama_kelas', 'like', "%{$s}%"))
                  ->orWhereHas('jurusan', fn ($j) => $j->where('nama_jurusan', 'like', "%{$s}%"))
                  ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        $siswas = $query->orderBy($sort, $dir)->paginate(15)->withQueryString();

        $kelasList   = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();
        $kelas       = $kelasList;
        $jurusan     = $jurusanList;

        return view('admin.siswa.index', compact('siswas', 'kelasList', 'jurusanList', 'kelas', 'jurusan', 'sortable', 'sort', 'dir'));
    }

    public function store(StoreSiswaRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'role'     => 'siswa',
                'nis_nip'  => $request->nis,
            ]);

            Siswa::create([
                'user_id'       => $user->id,
                'nis_nip'       => $request->nis,
                'kelas_id'      => $request->kelas_id,
                'jurusan_id'    => $request->jurusan_id,
                'nama_lengkap'  => $request->nama_lengkap,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat'        => $request->alamat,
                'no_telepon'    => $request->no_telepon,
                'status_aktif'  => true,
            ]);

            $this->auditLog->log(auth()->id(), 'create_siswa', 'siswa', $user->id);
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan. Password akun dikelola oleh SiPintu/Sijuna.');
    }

    public function edit(Siswa $siswa)
    {
        $kelasList   = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('admin.siswa.edit', compact('siswa', 'kelasList', 'jurusanList'));
    }

    public function update(StoreSiswaRequest $request, Siswa $siswa)
    {
        DB::transaction(function () use ($request, $siswa) {
            $siswa->user->update([
                'name'    => $request->name,
                'email'   => $request->email,
                'nis_nip' => $request->nis,
            ]);

            $siswa->update([
                'nis_nip'       => $request->nis,
                'kelas_id'      => $request->kelas_id,
                'jurusan_id'    => $request->jurusan_id,
                'nama_lengkap'  => $request->nama_lengkap,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat'        => $request->alamat,
                'no_telepon'    => $request->no_telepon,
            ]);

            $this->auditLog->log(auth()->id(), 'update_siswa', 'siswa', $siswa->id);
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->user->delete();
        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
}
