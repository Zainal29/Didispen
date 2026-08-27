<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\NotifikasiService;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        // Statistik
        $stats = [
            'total' => Dispensasi::whereDate('created_at', $today)->count(),
            'menunggu_keluar' => Dispensasi::where('status', 'disetujui')->whereDate('created_at', $today)->count(),
            'keluar' => Dispensasi::where('status', 'keluar')->whereDate('created_at', $today)->count(),
            'selesai' => Dispensasi::where('status', 'selesai')->whereDate('created_at', $today)->count(),
        ];

        // Data untuk setiap kategori
        $menungguKeluar = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'disetujui')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $siswaKeluar = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $selesai = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('status', 'selesai')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // ✅ PERBAIKAN: Hanya ambil riwayat yang dihubungi HARI INI agar tidak menumpuk
        $dihubungi = Dispensasi::with(['siswa.kelas.jurusan', 'guru'])
            ->where('is_warned', true)
            ->whereDate('warned_at', today())
            ->latest('warned_at')
            ->limit(30) // Maksimal 30 riwayat
            ->get();

        return view('satpam.dashboard', compact(
            'stats',
            'menungguKeluar',
            'siswaKeluar',
            'selesai',
            'dihubungi'
        ));
    }

    public function konfirmasiKembali(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'keluar') {
            return redirect()->back()->with('error', 'Dispensasi harus dalam status keluar untuk dikonfirmasi kembali.');
        }

        $dispensasi->update([
            'status' => 'selesai',
            'waktu_kembali_aktual' => now(),
            'satpam_kembali_id' => auth()->id(),
        ]);

        app(NotifikasiService::class)->send(
            $dispensasi->siswa->user_id,
            "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI. Terima kasih sudah kembali ke sekolah.",
            route('siswa.pengajuan.show', $dispensasi, false)
        );

        return redirect()->back()->with('success', "Siswa {$dispensasi->siswa->nama_lengkap} berhasil dikonfirmasi KEMBALI.");
    }

    public function showDetail(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.kelas.jurusan', 'guru']);

        return view('satpam.dispensasi-detail', compact('dispensasi'));
    }

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
