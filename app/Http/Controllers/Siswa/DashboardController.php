<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Notifikasi;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $siswa = auth()->user()->siswa;

        // ✅ Cari dispensasi aktif terbaru untuk ditampilkan QR Code-nya di dashboard
        $dispensasiAktif = Dispensasi::with(['guruPiket.guru', 'siswa.kelas.jurusan'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status', ['disetujui', 'keluar', 'selesai'])
            ->latest()
            ->first();

        // Statistik pengajuan
        $stats = [
            'total' => Dispensasi::where('siswa_id', $siswa->id)->count(),
            'menunggu' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'menunggu')->count(),
            'disetujui' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'disetujui')->count(),
            'ditolak' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'ditolak')->count(),
            'selesai' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'selesai')->count(),
        ];

        // Pengajuan terbaru (5 terakhir)
        $pengajuanTerbaru = Dispensasi::with(['guruPiket.guru'])
            ->where('siswa_id', $siswa->id)
            ->latest()
            ->take(5)
            ->get();

        // Notifikasi belum dibaca
        $notifikasiBelumDibaca = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return view('siswa.dashboard', compact(
            'stats', 
            'pengajuanTerbaru', 
            'notifikasiBelumDibaca',
            'dispensasiAktif'
        ));
    }
}