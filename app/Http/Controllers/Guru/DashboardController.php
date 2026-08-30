<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');

        // Filter aktif (default: 'semua')
        $filter = $request->get('filter', 'semua');

        // ==========================================
        // STATISTIK HARI INI
        // ==========================================
        $stats = [
            'menunggu' => Dispensasi::where('status', 'menunggu')->whereDate('created_at', $today)->count(),
            'disetujui' => Dispensasi::where('status', 'disetujui')->whereDate('created_at', $today)->count(),
            'keluar' => Dispensasi::where('status', 'keluar')->whereDate('created_at', $today)->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->whereDate('created_at', $today)->count(),
            'total' => Dispensasi::whereDate('created_at', $today)->count(),
        ];

        // ==========================================
        // QUERY UNTUK SETIAP KATEGORI
        // ==========================================

        // 1. Menunggu Persetujuan
        $menunggu = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('status', 'menunggu')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // 2. Sedang Keluar (sudah di-scan satpam)
        $sedangKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // 3. Selesai (sudah kembali)
        $selesai = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'selesai')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // 4. Terlambat (masih keluar tapi lewat batas waktu)
        $terlambat = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->where('batas_waktu_kembali', '<', now())
            ->latest()
            ->get();

        // 5. Disetujui (menunggu scan satpam)
        $disetujui = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'disetujui')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // ==========================================
        // TENTUKAN DATA YANG DITAMPILKAN
        // ==========================================
        $displayData = match($filter) {
            'menunggu' => $menunggu,
            'keluar' => $sedangKeluar,
            'selesai' => $selesai,
            'terlambat' => $terlambat,
            'disetujui' => $disetujui,
            default => $menunggu->merge($disetujui)->merge($sedangKeluar)->merge($selesai)->sortByDesc('created_at')->values(),
        };

        return view('guru.dashboard', compact(
            'stats',
            'filter',
            'menunggu',
            'sedangKeluar',
            'selesai',
            'terlambat',
            'disetujui',
            'displayData'
        ));
    }
}
