<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruPiket;
use Illuminate\Http\Request;

class GuruPiketController extends Controller
{
    public function index()
    {
        // Tampilkan 7 hari (Senin-Minggu)
        $piket = GuruPiket::with('guru.user')
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->get();
            
        $gurus = Guru::with('user')->get();

        return view('admin.piket.index', compact('piket', 'gurus'));
    }

    // Admin hanya update guru yang bertugas di hari tertentu
    public function update(Request $request, $id)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
        ]);

        $jadwal = GuruPiket::findOrFail($id);
        $jadwal->update(['guru_id' => $request->guru_id]);

        return redirect()->route('admin.piket.index')
            ->with('success', 'Jadwal piket berhasil diperbarui!');
    }
}