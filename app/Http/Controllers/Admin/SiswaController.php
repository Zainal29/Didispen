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
        $query = Siswa::with(['user', 'jurusan', 'kelas']);
        if ($request->filled('jurusan_id')) $query->where('jurusan_id', $request->jurusan_id);
        if ($request->filled('kelas_id')) $query->where('kelas_id', $request->kelas_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('nama_lengkap', 'like', "%{$s}%");
        }
        $siswas = $query->latest()->paginate(15);
        $jurusans = Jurusan::all();
        $kelas = Kelas::all();
        return view('admin.siswa.index', compact('siswas', 'jurusans', 'kelas'));
    }

    public function store(StoreSiswaRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
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
