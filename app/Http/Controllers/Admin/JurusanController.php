<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::withCount('kelas')->latest()->get();
        return view('admin.jurusan.index', compact('jurusans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_jurusan' => ['required', 'unique:jurusan,kode_jurusan'],
            'nama_jurusan' => ['required', 'string', 'max:255'],
        ]);
        Jurusan::create($data);
        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $data = $request->validate([
            'kode_jurusan' => ['required', 'unique:jurusan,kode_jurusan,' . $jurusan->id],
            'nama_jurusan' => ['required', 'string', 'max:255'],
        ]);
        $jurusan->update($data);
        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
