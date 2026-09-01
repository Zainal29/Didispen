<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');
        $filter = $request->get('filter', 'semua');

        // 1. STATISTIK HARI INI
        $stats = [
            'menunggu' => Dispensasi::where('status', 'menunggu')->whereDate('created_at', $today)->count(),
            'disetujui' => Dispensasi::where('status', 'disetujui')->whereDate('created_at', $today)->count(),
            'keluar' => Dispensasi::where('status', 'keluar')->whereDate('created_at', $today)->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->whereDate('created_at', $today)->count(),
            'total' => Dispensasi::whereDate('created_at', $today)->count(),
        ];

        // 2. QUERY DATA
        // Catatan: Untuk 'menunggu', guru_id masih null, jadi kita ambil semua yang menunggu hari ini
        $menunggu = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('status', 'menunggu')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $disetujui = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'disetujui')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $sedangKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $selesai = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'selesai')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $terlambat = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->where('batas_waktu_kembali', '<', now())
            ->latest()
            ->get();

        // 3. TENTUKAN DATA YANG DITAMPILKAN
        $displayData = match($filter) {
            'menunggu' => $menunggu,
            'keluar' => $sedangKeluar,
            'selesai' => $selesai,
            'terlambat' => $terlambat,
            'disetujui' => $disetujui,
            default => $menunggu->merge($disetujui)->merge($sedangKeluar)->merge($selesai)->sortByDesc('created_at')->values(),
        };

        return view('guru.dashboard', compact(
            'stats', 'filter', 'menunggu', 'sedangKeluar', 'selesai', 'terlambat', 'disetujui', 'displayData'
        ));
    }
}
