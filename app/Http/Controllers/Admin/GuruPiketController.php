<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\GuruPiket;
use Illuminate\Http\Request;

class GuruPiketController extends Controller
{
    /**
     * Tampilkan daftar jadwal piket
     */
    public function index()
    {
        // Urutkan berdasarkan tanggal terdekat, lalu berdasarkan shift (pagi/siang)
        $piket = GuruPiket::with('guru.user')
            ->orderBy('tanggal', 'asc')
            ->orderBy('shift', 'asc')
            ->get();
            
        $gurus = Guru::with('user')->get();

        return view('admin.piket.index', compact('piket', 'gurus'));
    }

    /**
     * Simpan jadwal piket baru (Fungsi yang baru ditambahkan)
     */
    public function store(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'in:pagi,siang'],
            'guru_id' => ['required', 'exists:guru,id'],
        ]);

        // Menggunakan updateOrCreate untuk mencegah duplikasi. 
        // Jika di tanggal dan shift tersebut sudah ada jadwal, maka akan di-update (ditimpa).
        GuruPiket::updateOrCreate(
            [
                'tanggal' => $request->tanggal,
                'shift' => $request->shift,
            ],
            [
                'guru_id' => $request->guru_id,
            ]
        );

        return redirect()->route('admin.piket.index')
            ->with('success', 'Jadwal piket baru berhasil ditambahkan!');
    }

    /**
     * Update guru yang bertugas di hari/shift tertentu
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'guru_id' => ['required', 'exists:guru,id'],
        ]);

        $jadwal = GuruPiket::findOrFail($id);
        
        // Ganti guru yang bertugas di jadwal tersebut
        $jadwal->update([
            'guru_id' => $request->guru_id,
        ]);

        return redirect()->route('admin.piket.index')
            ->with('success', 'Jadwal piket berhasil diperbarui!');
    }
}