<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\GuruPiket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $guru = auth()->user()->guru;

        // 1. Tentukan shift berdasarkan waktu saat ini
        $shiftIni = now()->hour < 12 ? 'pagi' : 'siang';
        $tanggalIni = today();

        // 2. ✅ Cari jadwal piket berdasarkan tanggal dan shift
        $piketHariIni = GuruPiket::with('guru')
            ->where('tanggal', $tanggalIni)
            ->where('shift', $shiftIni)
            ->first();

        if (!$piketHariIni) {
            return view('guru.dashboard', [
                'stats' => ['piket_hari_ini' => null, 'pending' => 0, 'keluar' => 0, 'selesai' => 0, 'total' => 0],
                'pendingDispensasi' => collect(),
                'siswaKeluar' => collect(),
            ]);
        }

        // Statistik
        $stats = [
            'piket_hari_ini' => $piketHariIni,
            'pending' => Dispensasi::where('guru_piket_id', $piketHariIni->id)->where('status', 'menunggu')->count(),
            'keluar' => Dispensasi::where('guru_piket_id', $piketHariIni->id)->where('status', 'keluar')->count(),
            'selesai' => Dispensasi::where('guru_piket_id', $piketHariIni->id)->where('status', 'selesai')->count(),
            'total' => Dispensasi::where('guru_piket_id', $piketHariIni->id)->whereDate('created_at', today())->count(),
        ];

        $pendingDispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('guru_piket_id', $piketHariIni->id)
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        $siswaKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('guru_piket_id', $piketHariIni->id)
            ->where('status', 'keluar')
            ->latest()
            ->get();

        return view('guru.dashboard', compact('stats', 'pendingDispensasi', 'siswaKeluar'));
    }
}