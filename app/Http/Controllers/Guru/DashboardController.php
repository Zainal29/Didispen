<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $guru = auth()->user()->guru;

        // Cek jadwal piket hari ini
        $piketHariIni = $guru->piket()
            ->where('tanggal', today())
            ->first();

        if (!$piketHariIni) {
            return view('guru.dashboard', [
                'stats' => [
                    'piket_hari_ini' => null,
                    'pending' => 0,
                    'keluar' => 0,
                    'selesai' => 0,
                    'total' => 0,
                ],
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

        // Pengajuan menunggu
        $pendingDispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('guru_piket_id', $piketHariIni->id)
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        // ✅ TAMBAHKAN: Siswa yang sedang keluar
        $siswaKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('guru_piket_id', $piketHariIni->id)
            ->where('status', 'keluar')
            ->latest()
            ->get();

        return view('guru.dashboard', compact('stats', 'pendingDispensasi', 'siswaKeluar'));
    }
}