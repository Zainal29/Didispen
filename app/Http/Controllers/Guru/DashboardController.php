<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $guru = Auth::user()->guru;

        // Statistik dasar
        $stats = [
            'piket_hari_ini' => $guru,
            'pending' => Dispensasi::where('status', 'menunggu')->count(),
            'keluar' => Dispensasi::where('status', 'keluar')->whereDate('created_at', today())->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->whereDate('created_at', today())->count(),
            'total' => Dispensasi::whereDate('created_at', today())->count(),

            // ✅ BARU: Statistik Overdue & Warning
            'overdue' => Dispensasi::where('status', 'keluar')
                ->whereNotNull('batas_waktu_kembali')
                ->where('batas_waktu_kembali', '<', now())
                ->count(),

            'warned' => Dispensasi::where('is_warned', true)
                ->whereDate('warned_at', today())
                ->count(),
        ];

        // Semua pengajuan yang masih menunggu
        $pendingDispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        // Siswa yang sedang keluar (untuk monitoring real-time)
        $siswaKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('status', 'keluar')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        // ✅ BARU: Siswa yang overdue (terlambat)
        $overdueDispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('status', 'keluar')
            ->whereNotNull('batas_waktu_kembali')
            ->where('batas_waktu_kembali', '<', now())
            ->latest('batas_waktu_kembali')
            ->get();

        return view('guru.dashboard', compact(
            'stats',
            'pendingDispensasi',
            'siswaKeluar',
            'overdueDispensasi'
        ));
    }
}
