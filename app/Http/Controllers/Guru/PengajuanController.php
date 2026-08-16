<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\GuruPiket;
use App\Services\DispensasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PengajuanController extends Controller
{
    public function __construct(private DispensasiService $dispensasiService) {}

    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        
        // ✅ REVISI: Gunakan scope 'hariIni()' alih-alih where('tanggal', today())
        $piketHariIni = GuruPiket::with('guru')
            ->where('guru_id', $guru->id)
            ->hariIni() // <-- INI KUNCINYA: Otomatis mencari hari ini (senin, selasa, dst)
            ->first();

        if (!$piketHariIni) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Anda tidak memiliki jadwal piket hari ini. Hubungi admin untuk penugasan.');
        }

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru'])
            ->where('guru_piket_id', $piketHariIni->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dispensasi = $query->latest()->paginate(15);

        return view('guru.pengajuan.index', compact('dispensasi', 'piketHariIni'));
    }

    public function show(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

        // Keamanan: Pastikan guru yang login adalah guru yang dijadwalkan di dispensasi ini
        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak. Anda bukan guru piket yang menangani dispensasi ini.');
        }

        return view('guru.pengajuan.show', compact('dispensasi'));
    }

    public function approve(Request $request, Dispensasi $dispensasi)
    {
        $dispensasi->load(['guruPiket', 'siswa.kelas']);

        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak.');
        }

        try {
            // ✅ QR Code berisi URL verifikasi (bisa di-scan langsung dari HP Satpam)
            $qrContent = url('/verifikasi/' . $dispensasi->nomor_surat);
            $qrCodePath = 'qr_codes/dispensasi_' . $dispensasi->id . '.svg';

            if (!Storage::disk('public')->exists('qr_codes')) {
                Storage::disk('public')->makeDirectory('qr_codes');
            }

            Storage::disk('public')->put(
                $qrCodePath,
                QrCode::format('svg')->size(300)->generate($qrContent)
            );

            $dispensasi->update([
                'status' => 'disetujui',
                'qr_code' => $qrCodePath
            ]);

            // Kirim notifikasi ke siswa
            app(\App\Services\NotifikasiService::class)->send(
                $dispensasi->siswa->user_id,
                "✅ Pengajuan dispensasi ({$dispensasi->nomor_surat}) telah DISETUJUI oleh Guru Piket.",
                route('siswa.pengajuan.show', $dispensasi)
            );

            return redirect()->route('guru.pengajuan.show', $dispensasi)
                ->with('success', "Dispensasi {$dispensasi->nomor_surat} berhasil disetujui. QR Code telah dibuat.");

        } catch (\Exception $e) {
            Log::error('Gagal generate QR Code Dispensasi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat QR Code. Silakan coba lagi.');
        }
    }

    public function reject(Request $request, Dispensasi $dispensasi)
    {
        $dispensasi->load('guruPiket');

        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'catatan_admin' => 'required|string|min:5'
        ]);

        $this->dispensasiService->reject($dispensasi, auth()->user()->guru, $request->catatan_admin);

        return redirect()->route('guru.pengajuan.index')
            ->with('success', "Dispensasi {$dispensasi->nomor_surat} ditolak.");
    }
}