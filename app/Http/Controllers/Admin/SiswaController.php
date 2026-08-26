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
            'nama_lengkap' => 'Nama Siswa',
            'nis_nip'      => 'NIS / NISN',
            'created_at'   => 'Terdaftar',
        ];

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir', 'desc');

        if (! array_key_exists($sort, $sortable)) {
            $sort = 'created_at';
        }

        if (! in_array($dir, ['asc', 'desc'])) {
            $dir = 'desc';
        }

        $query = Siswa::with(['user', 'jurusan', 'kelas.jurusan']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($u) use ($s) {
                      $u->where('nis_nip', 'like', "%{$s}%")
                       ->orWhere('email', 'like', "%{$s}%")
                       ->orWhere('name', 'like', "%{$s}%");
                  });
            });
        }

        // ✅ Filter per kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($sort === 'nis_nip') {
            $query->join('users', 'siswa.user_id', '=', 'users.id')
                  ->orderBy('users.nis_nip', $dir)
                  ->select('siswa.*');
        } else {
            $query->orderBy($sort, $dir);
        }

        $siswas = $query->paginate(15)->withQueryString();

        // ✅ Kirim data kelas & jurusan untuk dropdown modal
        $kelass    = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $jurusans  = Jurusan::orderBy('nama_jurusan')->get();

        return view('admin.siswa.index', compact('siswas', 'sortable', 'sort', 'dir', 'kelass', 'jurusans'));
    }

    public function store(StoreSiswaRequest $request)
    {
        DB::transaction(function () use ($request) {
            // ✅ REVISI SIPINTU: Password dikelola SiPintu (sudah di-hash saat sinkronisasi).
            // Untuk pembuatan manual oleh admin, gunakan password acak yang tidak dapat ditekan
            // dan TIDAK diberitahukan ke siapa pun. must_change_password DIABAIKAN (false).
            $user = User::create([
                'name'                 => $request->nama_lengkap,
                'email'                => $request->email,
                'password'             => Hash::make(bin2hex(random_bytes(16))),
                'role'                 => 'siswa',
                'nis_nip'              => $request->nis_nip,
                'must_change_password' => false,
            ]);

            // ✅ REVISI SIPINTU: no_telepon & alamat tidak diinput manual
            // (data sudah tersinkronisasi otomatis dari SiPintu)
            Siswa::create([
                'user_id'       => $user->id,
                'jurusan_id'    => $request->jurusan_id,
                'kelas_id'      => $request->kelas_id,
                'nama_lengkap'  => $request->nama_lengkap,
                'tanggal_lahir' => $request->tanggal_lahir,
            ]);

            $this->auditLog->log(auth()->id(), 'create_siswa', 'siswa', $user->id);
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan. Password akun dikelola oleh SiPintu/Sijuna.');
    }

    public function edit(Siswa $siswa)
    {
        return response()->json($siswa->load('user'));
    }

    public function update(StoreSiswaRequest $request, Siswa $siswa)
    {
        DB::transaction(function () use ($request, $siswa) {
            // ✅ FIX BUG: sebelumnya memakai $request->name yang tidak ada di StoreSiswaRequest
            $siswa->user->update([
                'name'    => $request->nama_lengkap,
                'email'   => $request->email,
                'nis_nip' => $request->nis_nip,
            ]);

            // ✅ REVISI SIPINTU: Password TIDAK diubah dari sini (dikelola SiPintu).
            // ✅ REVISI SIPINTU: no_telepon & alamat tidak diedit manual (dari sinkronisasi SiPintu).
            $siswa->update($request->only([
                'jurusan_id', 'kelas_id', 'nama_lengkap',
                'tanggal_lahir',
            ]));
        });

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->user->delete(); // cascade ke siswa
        return redirect()->route('admin.siswa.index')
            ->with('success', 'Siswa berhasil dihapus.');
    }
}