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
        $search = $request->get('search', ''); // <i class="fas fa-check-circle"></i> BARU: Ambil kata kunci pencarian

        // 1. STATISTIK HARI INI (Tetap global untuk hari ini)
        $stats = [
            'menunggu' => Dispensasi::where('status', 'menunggu')->whereDate('created_at', $today)->count(),
            'disetujui' => Dispensasi::where('status', 'disetujui')->whereDate('created_at', $today)->count(),
            'keluar' => Dispensasi::where('status', 'keluar')->whereDate('created_at', $today)->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->whereDate('created_at', $today)->count(),
            'total' => Dispensasi::whereDate('created_at', $today)->count(),
        ];

        // 2. QUERY DASAR DENGAN PENCARIAN <i class="fas fa-check-circle"></i>
        $baseQuery = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->whereDate('created_at', $today);

        // Jika ada kata kunci pencarian, filter query-nya
        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhereHas('user', function($q3) use ($search) {
                             $q3->where('nis_nip', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // 3. CLONE QUERY UNTUK SETIAP KATEGORI (Agar search berlaku di semua tab)
        $menunggu = (clone $baseQuery)->where('status', 'menunggu')->latest()->get();
        $disetujui = (clone $baseQuery)->where('status', 'disetujui')->latest()->get();
        $sedangKeluar = (clone $baseQuery)->where('status', 'keluar')->latest()->get();
        $selesai = (clone $baseQuery)->where('status', 'selesai')->latest()->get();
        $terlambat = (clone $baseQuery)->where('status', 'keluar')->where('batas_waktu_kembali', '<', now())->latest()->get();

        // 4. TENTUKAN DATA YANG DITAMPILKAN
        $displayData = match($filter) {
            'menunggu' => $menunggu,
            'keluar' => $sedangKeluar,
            'selesai' => $selesai,
            'terlambat' => $terlambat,
            'disetujui' => $disetujui,
            default => $menunggu->merge($disetujui)->merge($sedangKeluar)->merge($selesai)->sortByDesc('created_at')->values(),
        };

        $dihubungi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('is_warned', true)
            ->whereDate('warned_at', today())
            ->latest('warned_at')
            ->limit(30)
            ->get();

        return view('guru.dashboard', compact(
            'stats', 'filter', 'search', 'menunggu', 'sedangKeluar', 'selesai', 'terlambat', 'disetujui', 'displayData', 'dihubungi'
        ));
    }

    /**
     * Tandai dispensasi sudah dihubungi via WhatsApp
     */
    public function markWaContacted(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'keluar') {
            return response()->json(['success' => false, 'message' => 'Dispensasi tidak valid']);
        }

        $dispensasi->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
