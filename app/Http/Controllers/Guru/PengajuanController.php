<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
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
        $piketHariIni = $guru->piket()->where('tanggal', today())->first();

        if (!$piketHariIni) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Anda tidak memiliki jadwal piket hari ini.');
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

        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak. Anda bukan guru piket yang menangani dispensasi ini.');
        }

        return view('guru.pengajuan.show', compact('dispensasi'));
    }

    public function approve(Request $request, Dispensasi $dispensasi)
    {
        // Load relasi yang dibutuhkan untuk data QR
        $dispensasi->load(['guruPiket', 'siswa.kelas']);

        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak.');
        }

        try {
            // 1. Siapkan data untuk QR Code
            $qrData = [
                'id' => $dispensasi->id,
                'nomor_surat' => $dispensasi->nomor_surat,
                'siswa' => $dispensasi->siswa->nama_lengkap ?? 'Siswa',
                'kelas' => $dispensasi->siswa->kelas->nama_kelas ?? '-',
                'jam_keluar' => $dispensasi->jam_keluar,
                'jam_kembali' => $dispensasi->jam_kembali,
                'berlaku_sampai' => now()->endOfDay()->toIso8601String(),
                'token' => md5($dispensasi->id . $dispensasi->nomor_surat . 'SECRET_KEY_DISPENSI')
            ];

            $qrCodePath = 'qr_codes/dispensasi_' . $dispensasi->id . '.svg';

            // 2. Pastikan folder penyimpanan ada
            if (!Storage::disk('public')->exists('qr_codes')) {
                Storage::disk('public')->makeDirectory('qr_codes');
            }

            // 3. Generate dan simpan QR Code (Format SVG agar tajam & ringan)
            Storage::disk('public')->put(
                $qrCodePath,
                QrCode::format('svg')->size(300)->generate(json_encode($qrData))
            );

            // 4. Update status DAN path qr_code sekaligus (Hanya 1x query ke database)
            $dispensasi->update([
                'status' => 'disetujui',
                'qr_code' => $qrCodePath
            ]);

            return redirect()->route('guru.pengajuan.show', $dispensasi)
                ->with('success', "Dispensasi {$dispensasi->nomor_surat} berhasil disetujui. QR Code telah dibuat.");

        } catch (\Exception $e) {
            // Catat error di log Laravel untuk debugging
            Log::error('Gagal generate QR Code Dispensasi: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal membuat QR Code. Silakan coba lagi atau hubungi admin.');
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

    /*
     * ✅ CATATAN PENTING:
     * Method `konfirmasiKeluar` dan `konfirmasiKembali` sengaja DIHAPUS dari controller ini.
     * Alasannya: Konfirmasi fisik keluar/masuk siswa sekarang sepenuhnya menjadi
     * tanggung jawab SATPAM melalui fitur Scan QR Code, bukan Guru Piket.
     */
}
