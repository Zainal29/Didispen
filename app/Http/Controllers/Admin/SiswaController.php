<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiswaRequest;
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
        if (!array_key_exists($sort, $sortable)) $sort = 'created_at';
        if (!in_array($dir, ['asc', 'desc'])) $dir = 'desc';

        $query = Siswa::with(['user', 'jurusan', 'kelas']);
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

        if ($sort === 'nis_nip') {
            $query->join('users', 'siswa.user_id', '=', 'users.id')
                  ->orderBy('users.nis_nip', $dir)
                  ->select('siswa.*');
        } else {
            $query->orderBy($sort, $dir);
        }

        $siswas = $query->paginate(15)->withQueryString();
        return view('admin.siswa.index', compact('siswas', 'sortable', 'sort', 'dir'));
    }

    public function store(StoreSiswaRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password ?? 'password'),
                'role' => 'siswa',
                'nis_nip' => $request->nis_nip,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'jurusan_id' => $request->jurusan_id,
                'kelas_id' => $request->kelas_id,
                'nama_lengkap' => $request->nama_lengkap,
                'tanggal_lahir' => $request->tanggal_lahir,
                'alamat' => $request->alamat,
                'no_telepon' => $request->no_telepon,
            ]);

            $this->auditLog->log(auth()->id(), 'create_siswa', 'siswa', $user->id);
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        return response()->json($siswa->load('user'));
    }

    public function update(StoreSiswaRequest $request, Siswa $siswa)
    {
        DB::transaction(function () use ($request, $siswa) {
            $siswa->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'nis_nip' => $request->nis_nip,
            ]);
            if ($request->filled('password')) {
                $siswa->user->update(['password' => Hash::make($request->password)]);
            }
            $siswa->update($request->only(['jurusan_id', 'kelas_id', 'nama_lengkap', 'tanggal_lahir', 'alamat', 'no_telepon']));
        });

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->user->delete(); // cascade ke siswa
        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }
}