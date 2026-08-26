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
            'hari_ini' => Dispensasi::whereDate('created_at', $today)->count(),
        ];

        // Data untuk setiap kategori
        $menungguKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'disetujui')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        $siswaKeluar = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // Siswa yang sudah kembali (selesai)
        $selesai = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('status', 'selesai')
            ->whereDate('created_at', $today)
            ->latest()
            ->get();

        // ✅ PERBAIKAN: Hapus whereDate('created_at') agar menampilkan SEMUA riwayat yang sudah dihubungi
        $dihubungi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('is_warned', true)
            ->latest('warned_at') // Urutkan dari yang terbaru dihubungi
            ->get();

        return view('satpam.dashboard', compact(
            'stats',
            'menungguKeluar',
            'siswaKeluar',
            'selesai',
            'dihubungi'
        ));
    }

    /**
     * Konfirmasi manual siswa KELUAR (Diharuskan via Scan QR)
     */
    public function konfirmasiKeluar(Dispensasi $dispensasi)
    {
        return redirect()->route('satpam.scan')
            ->with('warning', 'Konfirmasi siswa keluar wajib dilakukan dengan meng-scan QR Code dispensasi siswa.');
    }

    /**
     * Konfirmasi manual siswa KEMBALI (tanpa scan QR)
     */
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

        // Kirim notifikasi ke siswa bahwa status dispensasi telah SELESAI
        app(NotifikasiService::class)->send(
            $dispensasi->siswa->user_id,
            "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI. Terima kasih sudah kembali ke sekolah tepat waktu.",
            route('siswa.pengajuan.show', $dispensasi, false)
        );

        return redirect()->back()->with('success', "Siswa {$dispensasi->siswa->nama_lengkap} berhasil dikonfirmasi KEMBALI.");
    }

    /**
     * Tampilkan detail dispensasi
     */
    public function showDetail(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        return view('satpam.dispensasi-detail', compact('dispensasi'));
    }

    /**
     * Tandai dispensasi sudah dihubungi
     */
    public function markContacted(Dispensasi $dispensasi)
    {
        if ($dispensasi->status !== 'keluar') {
            return back()->with('error', 'Dispensasi ini tidak valid untuk ditandai sudah dihubungi.');
        }

        $dispensasi->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);

        return back()->with('success', 'Dispensasi berhasil ditandai sudah dihubungi.');
    }

    /**
     * Tandai sudah dihubungi via WhatsApp (dipanggil via AJAX)
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
