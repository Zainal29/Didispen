<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $siswa = auth()->user()->siswa;

        // ✅ Cari dispensasi disetujui yang belum di-scan untuk ditampilkan QR Code-nya di dashboard
        $dispensasiAktif = Dispensasi::with(['guru', 'siswa.kelas.jurusan'])
            ->where('siswa_id', $siswa->id)
            ->where('status', 'disetujui')
            ->latest()
            ->first();

        // ✅ TAMBAHKAN INI: Auto-generate QR Code jika status disetujui tapi qr_code masih kosong
        if ($dispensasiAktif && $dispensasiAktif->status === 'disetujui' && empty($dispensasiAktif->qr_code)) {
            if (empty($dispensasiAktif->qr_token)) {
                $dispensasiAktif->qr_token = Str::random(64);
            }

            // ✅ SESUDAH (Ganti dengan ini):
            $qrContent = $dispensasiAktif->qr_token; // Hanya token murni
            // ✅ PERBAIKAN 1: Gunakan ekstensi .png agar lebih stabil di tag <img>
            $qrCodePath = 'qr_codes/dispensasi_' . $dispensasiAktif->id . '.svg';

            // Buat direktori jika belum ada
            Storage::disk('public')->makeDirectory('qr_codes');

            // ✅ PERBAIKAN 2: Tambahkan slash '/' setelah 'public' agar path menjadi app/public/qr_codes/...
            QrCode::format('svg')
                ->size(300)
                ->margin(0)
                ->generate($qrContent, storage_path('app/public/' . $qrCodePath));

            $dispensasiAktif->qr_code = $qrCodePath;
            $dispensasiAktif->save();
        }

        // Statistik pengajuan
        $stats = [
            'total' => Dispensasi::where('siswa_id', $siswa->id)->count(),
            'menunggu' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'menunggu')->count(),
            'disetujui' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'disetujui')->count(),
            'ditolak' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'ditolak')->count(),
            'selesai' => Dispensasi::where('siswa_id', $siswa->id)->where('status', 'selesai')->count(),
        ];

        // Pengajuan terbaru (5 terakhir)
        $pengajuanTerbaru = Dispensasi::with(['guru'])
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
