<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruPiket;
use Illuminate\Http\Request;

class GuruPiketController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $piket = GuruPiket::with('guru.user')
            ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('tanggal')->orderBy('shift')->get();
        $gurus = Guru::with('user')->get();
        return view('admin.piket.index', compact('piket', 'gurus', 'date'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guru_id' => ['required', 'exists:guru,id'],
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'in:pagi,siang'],
        ]);
        GuruPiket::updateOrCreate(
            ['tanggal' => $data['tanggal'], 'shift' => $data['shift']],
            ['guru_id' => $data['guru_id']]
        );
        return redirect()->route('admin.piket.index')->with('success', 'Jadwal piket disimpan.');
    }

    public function destroy(GuruPiket $piket)
    {
        $piket->delete();
        return redirect()->route('admin.piket.index')->with('success', 'Jadwal dihapus.');
    }
}
