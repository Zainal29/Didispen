<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Services\DispensasiService;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function __construct(private DispensasiService $dispensasiService) {}

    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        
        $piketHariIni = $guru->piket()
            ->where('tanggal', today())
            ->first();

        if (!$piketHariIni) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Anda tidak memiliki jadwal piket hari ini.');
        }

        $query = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
            ->where('guru_piket_id', $piketHariIni->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $dispensasi = $query->latest()->paginate(15);

        return view('guru.pengajuan.index', compact('dispensasi', 'piketHariIni'));
    }

    public function show(Dispensasi $dispensasi)
    {
        // Load relationships dengan nama yang BENAR (camelCase)
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

        // ✅ PERBAIKAN: Cek apakah guruPiket ada DAN milik guru yang login
        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak. Anda bukan guru piket yang menangani dispensasi ini.');
        }

        return view('guru.pengajuan.show', compact('dispensasi'));
    }

    public function approve(Request $request, Dispensasi $dispensasi)
    {
        $dispensasi->load('guruPiket');
        
        if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
            abort(403, 'Akses ditolak.');
        }

        $this->dispensasiService->approve($dispensasi, auth()->user()->guru, $request->catatan_admin ?? '');

        return redirect()->route('guru.pengajuan.index')
            ->with('success', "Dispensasi {$dispensasi->nomor_surat} berhasil disetujui.");
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

    // Tambahkan method ini di dalam class PengajuanController

public function konfirmasiKeluar(Dispensasi $dispensasi)
{
    $dispensasi->load('guruPiket');
    
    if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
        abort(403, 'Akses ditolak.');
    }

    if ($dispensasi->status !== 'disetujui') {
        return redirect()->back()->with('error', 'Dispensasi harus dalam status disetujui.');
    }

    $dispensasi->update(['status' => 'keluar']);

    return redirect()->route('guru.pengajuan.index')
        ->with('success', "Siswa {$dispensasi->siswa->nama_lengkap} dikonfirmasi keluar.");
}

public function konfirmasiKembali(Dispensasi $dispensasi)
{
    $dispensasi->load('guruPiket');
    
    if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru->id) {
        abort(403, 'Akses ditolak.');
    }

    if ($dispensasi->status !== 'keluar') {
        return redirect()->back()->with('error', 'Dispensasi harus dalam status keluar.');
    }

    $dispensasi->update(['status' => 'selesai']);

    return redirect()->route('guru.pengajuan.index')
        ->with('success', "Siswa {$dispensasi->siswa->nama_lengkap} dikonfirmasi kembali.");
}

}