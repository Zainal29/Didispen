<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with('jurusan')->withCount('siswa');
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }
        $kelas = $query->latest()->get();
        $jurusans = Jurusan::all();
        return view('admin.kelas.index', compact('kelas', 'jurusans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'in:X,XI,XII'],
        ]);
        Kelas::create($data);
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'in:X,XI,XII'],
        ]);
        $kelas->update($data);
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
