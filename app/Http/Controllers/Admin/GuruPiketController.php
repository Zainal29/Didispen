<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Guru;
use Illuminate\Http\Request;

class GuruPiketController extends Controller
{
    /**
     * Halaman Rekap & Riwayat Guru Piket
     */
    public function index()
    {
        // Ambil semua guru dan hitung berapa banyak dispensasi yang pernah mereka proses
        $gurus = Guru::with('user')
            ->withCount('dispensasi')
            ->orderByDesc('dispensasi_count')
            ->get();

        // Ambil 100 riwayat dispensasi terakhir beserta guru yang memprosesnya
        $riwayat = Dispensasi::with(['guru', 'siswa.kelas.jurusan'])
            ->whereNotNull('guru_id')
            ->latest()
            ->limit(100)
            ->get();

        $piket = collect(); // collection kosong untuk kompatibilitas

        return view('admin.piket.index', compact('gurus', 'riwayat', 'piket'));
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.piket.index')
            ->with('info', 'Sistem menggunakan konsep "Login = Piket". Tidak ada jadwal piket manual yang perlu ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('admin.piket.index')
            ->with('info', 'Sistem menggunakan konsep "Login = Piket". Tidak ada jadwal piket yang perlu diubah.');
    }

    public function destroy($id)
    {
        return redirect()->route('admin.piket.index')
            ->with('info', 'Sistem menggunakan konsep "Login = Piket". Data rekap tidak dapat dihapus manual dari menu ini.');
    }
}
