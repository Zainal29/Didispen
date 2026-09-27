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
        $menungguCount = Dispensasi::where('status', 'menunggu')->whereDate('created_at', $today)->count();
        $stats = [
            'menunggu'  => $menungguCount,
            'pending'   => $menungguCount,
            'disetujui' => Dispensasi::where('status', 'disetujui')->whereDate('created_at', $today)->count(),
            'keluar'    => Dispensasi::where('status', 'keluar')->whereDate('created_at', $today)->count(),
            'selesai'   => Dispensasi::where('status', 'selesai')->whereDate('created_at', $today)->count(),
            'terlambat' => Dispensasi::where('status', 'keluar')->where('batas_waktu_kembali', '<', now())->whereDate('created_at', $today)->count(),
            'total'     => Dispensasi::whereDate('created_at', $today)->count(),
        ];

        // 2. QUERY DASAR DENGAN PENCARIAN
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

        // 3. TENTUKAN DATA YANG DITAMPILKAN SECARA EFISIEN LANGSUNG DARI DATABASE
        $displayQuery = clone $baseQuery;
        $displayData = match($filter) {
            'menunggu'  => $displayQuery->where('status', 'menunggu')->latest()->get(),
            'keluar'    => $displayQuery->where('status', 'keluar')->latest()->get(),
            'selesai'   => $displayQuery->where('status', 'selesai')->latest()->get(),
            'terlambat' => $displayQuery->where('status', 'keluar')->where('batas_waktu_kembali', '<', now())->latest()->get(),
            'disetujui' => $displayQuery->where('status', 'disetujui')->latest()->get(),
            'dihubungi' => $displayQuery->where('is_warned', true)->latest('warned_at')->get(),
            default     => $displayQuery->latest()->get(),
        };

        $menunggu = collect();
        $disetujui = collect();
        $sedangKeluar = collect();
        $selesai = collect();
        $terlambat = collect();

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
