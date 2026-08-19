<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Siswa yang sudah disetujui tapi belum keluar (menunggu konfirmasi keluar)
        $menungguKeluar = Dispensasi::with(['siswa.kelas.jurusan', 'guruPiket.guru'])
            ->where('status', 'disetujui')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        // Siswa yang sedang keluar (belum kembali)
        $siswaKeluar = Dispensasi::with(['siswa.kelas.jurusan', 'guruPiket.guru'])
            ->where('status', 'keluar')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        // Statistik
        $stats = [
            'menunggu_keluar' => $menungguKeluar->count(),
            'total_keluar' => $siswaKeluar->count(),
            'hari_ini' => Dispensasi::whereDate('created_at', today())->count(),
            'selesai' => Dispensasi::whereDate('created_at', today())->where('status', 'selesai')->count(),
        ];

        return view('satpam.dashboard', compact('menungguKeluar', 'siswaKeluar', 'stats'));
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
            'satpam_kembali_id' => auth()->id()
        ]);

        // Kirim notifikasi ke siswa bahwa status dispensasi telah SELESAI
        app(\App\Services\NotifikasiService::class)->send(
            $dispensasi->siswa->user_id,
            "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI. Terima kasih sudah kembali ke sekolah tepat waktu.",
            route('siswa.pengajuan.show', $dispensasi, false)
        );

        return redirect()->back()->with('success', "Siswa {$dispensasi->siswa->nama_lengkap} berhasil dikonfirmasi KEMBALI.");
    }
}