<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Organisasi;
use App\Models\Siswa;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index(Request $request)
    {
        $query = Organisasi::with(['ketua.kelas', 'ketua.user', 'pembina.user']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('nama_organisasi', 'like', "%{$s}%")
                    ->orWhere('singkatan', 'like', "%{$s}%")
                    ->orWhere('kategori', 'like', "%{$s}%")
                    ->orWhereHas('ketua', function ($sk) use ($s) {
                        $sk->where('nama_lengkap', 'like', "%{$s}%")
                            ->orWhere('nis_nip', 'like', "%{$s}%");
                    })
                    ->orWhereHas('pembina', function ($gp) use ($s) {
                        $gp->where('nama_lengkap', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $organisasis = $query->orderBy('nama_organisasi', 'asc')->paginate(15)->withQueryString();
        $gurus = Guru::where('status_aktif', true)->orderBy('nama_lengkap')->get(['id', 'nama_lengkap']);

        $isSuperAdmin = auth()->user()->isSuperAdmin();

        return view('admin.organisasi.index', compact('organisasis', 'gurus', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan menambah data organisasi.');
        }

        $validated = $request->validate([
            'nama_organisasi' => 'required|string|max:100|unique:organisasi,nama_organisasi',
            'singkatan'       => 'nullable|string|max:30',
            'kategori'        => 'required|in:osis,ekstrakurikuler,kejuruan,komunitas,lainnya',
            'ketua_siswa_id'  => 'nullable|exists:siswa,id',
            'pembina_guru_id' => 'nullable|exists:guru,id',
            'deskripsi'       => 'nullable|string|max:500',
            'status_aktif'    => 'boolean',
        ], [
            'nama_organisasi.required' => 'Nama organisasi wajib diisi.',
            'nama_organisasi.unique'   => 'Nama organisasi sudah terdaftar.',
            'kategori.required'        => 'Kategori wajib dipilih.',
            'ketua_siswa_id.exists'    => 'Siswa ketua yang dipilih tidak valid.',
            'pembina_guru_id.exists'   => 'Guru pembina yang dipilih tidak valid.',
        ]);

        $organisasi = Organisasi::create($validated);

        $this->auditLog->log(
            auth()->id(),
            'create_organisasi',
            'organisasi',
            $organisasi->id,
            null,
            ['nama' => $organisasi->nama_organisasi, 'ketua_id' => $organisasi->ketua_siswa_id]
        );

        return redirect()->route('admin.organisasi.index')
            ->with('success', "Organisasi {$organisasi->nama_organisasi} berhasil ditambahkan.");
    }

    public function edit(Organisasi $organisasi)
    {
        $organisasi->load(['ketua.kelas', 'pembina']);
        return response()->json([
            'id'              => $organisasi->id,
            'nama_organisasi' => $organisasi->nama_organisasi,
            'singkatan'       => $organisasi->singkatan,
            'kategori'        => $organisasi->kategori,
            'ketua_siswa_id'  => $organisasi->ketua_siswa_id,
            'ketua_nama'      => $organisasi->ketua ? "{$organisasi->ketua->nama_lengkap} ({$organisasi->ketua->kelas?->nama_kelas})" : null,
            'pembina_guru_id' => $organisasi->pembina_guru_id,
            'deskripsi'       => $organisasi->deskripsi,
            'status_aktif'    => (bool) $organisasi->status_aktif,
        ]);
    }

    public function update(Request $request, Organisasi $organisasi)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan mengubah organisasi dan menunjuk ketua.');
        }

        $validated = $request->validate([
            'nama_organisasi' => 'required|string|max:100|unique:organisasi,nama_organisasi,' . $organisasi->id,
            'singkatan'       => 'nullable|string|max:30',
            'kategori'        => 'required|in:osis,ekstrakurikuler,kejuruan,komunitas,lainnya',
            'ketua_siswa_id'  => 'nullable|exists:siswa,id',
            'pembina_guru_id' => 'nullable|exists:guru,id',
            'deskripsi'       => 'nullable|string|max:500',
            'status_aktif'    => 'boolean',
        ], [
            'nama_organisasi.required' => 'Nama organisasi wajib diisi.',
            'nama_organisasi.unique'   => 'Nama organisasi sudah terdaftar.',
            'kategori.required'        => 'Kategori wajib dipilih.',
            'ketua_siswa_id.exists'    => 'Siswa ketua yang dipilih tidak valid.',
            'pembina_guru_id.exists'   => 'Guru pembina yang dipilih tidak valid.',
        ]);

        $oldData = $organisasi->toArray();
        $organisasi->update($validated);

        $this->auditLog->log(
            auth()->id(),
            'update_organisasi',
            'organisasi',
            $organisasi->id,
            $oldData,
            $organisasi->toArray()
        );

        return redirect()->route('admin.organisasi.index')
            ->with('success', "Data organisasi {$organisasi->nama_organisasi} berhasil diperbarui.");
    }

    public function destroy(Organisasi $organisasi)
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan menghapus organisasi.');
        }

        $nama = $organisasi->nama_organisasi;
        $id = $organisasi->id;
        $organisasi->delete();

        $this->auditLog->log(auth()->id(), 'delete_organisasi', 'organisasi', $id, ['nama' => $nama], null);

        return redirect()->route('admin.organisasi.index')
            ->with('success', "Organisasi {$nama} berhasil dihapus.");
    }

    /**
     * API pencarian siswa untuk penunjukan ketua ekstra (Autocomplete)
     */
    public function cariSiswa(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $siswas = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->where(function ($query) use ($q) {
                $query->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('nis_nip', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id'   => $s->id,
                    'nis'  => $s->nis_nip,
                    'nama' => $s->nama_lengkap,
                    'kelas'=> $s->kelas?->nama_kelas ?? 'Belum ada kelas',
                ];
            });

        return response()->json($siswas);
    }
}
