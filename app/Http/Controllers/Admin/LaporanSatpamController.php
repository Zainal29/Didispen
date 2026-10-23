<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanSatpam;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;

class LaporanSatpamController extends Controller
{
    public function __construct(
        private NotifikasiService $notifikasiService
    ) {}

    /**
     * Halaman daftar laporan temuan dari satpam di panel admin
     */
    public function index(Request $request)
    {
        $status   = $request->query('status');
        $kategori = $request->query('kategori');
        $search   = $request->query('q');
        $tanggal  = $request->query('tanggal');

        $query = LaporanSatpam::with(['siswa.kelas.jurusan', 'dispensasi', 'satpam', 'petugasTindakLanjut'])
            ->latest();

        if ($status && in_array($status, ['menunggu', 'ditindaklanjuti', 'selesai'])) {
            $query->where('status', $status);
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($tanggal) {
            $query->whereDate('created_at', $tanggal);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sq) use ($search) {
                    $sq->where('nama_lengkap', 'like', "%{$search}%")
                       ->orWhereHas('user', fn($uq) => $uq->where('nis_nip', 'like', "%{$search}%"));
                })->orWhereHas('dispensasi', function ($dq) use ($search) {
                    $dq->where('nomor_surat', 'like', "%{$search}%");
                })->orWhereHas('satpam', function ($stq) use ($search) {
                    $stq->where('name', 'like', "%{$search}%");
                })->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $laporan = $query->paginate(20)->withQueryString();

        $stats = [
            'total'           => LaporanSatpam::count(),
            'menunggu'        => LaporanSatpam::where('status', 'menunggu')->count(),
            'ditindaklanjuti' => LaporanSatpam::where('status', 'ditindaklanjuti')->count(),
            'selesai'        => LaporanSatpam::where('status', 'selesai')->count(),
        ];

        return view('admin.laporan-satpam.index', compact('laporan', 'stats'));
    }

    /**
     * Tindak lanjuti laporan dari satpam (oleh Admin / Guru BK)
     */
    public function tindakLanjuti(Request $request, LaporanSatpam $laporanSatpam)
    {
        $validated = $request->validate([
            'status'                 => 'required|in:ditindaklanjuti,selesai',
            'catatan_tindak_lanjut' => 'required|string|min:3|max:1000',
        ], [
            'status.required'                => 'Pilih status tindak lanjut.',
            'catatan_tindak_lanjut.required' => 'Catatan tindak lanjut wajib diisi.',
        ]);

        $laporanSatpam->update([
            'status'                 => $validated['status'],
            'catatan_tindak_lanjut' => $validated['catatan_tindak_lanjut'],
            'tindak_lanjut_oleh'     => auth()->id(),
            'tindak_lanjut_at'       => now(),
        ]);

        // Beri kabar ke satpam yang melapor
        if ($laporanSatpam->satpam_id) {
            try {
                $statusText = $validated['status'] === 'selesai' ? 'SELESAI DITANGANI' : 'SEDANG DITINDAKLANJUTI';
                $this->notifikasiService->send(
                    $laporanSatpam->satpam_id,
                    "Laporan temuan gerbang siswa {$laporanSatpam->siswa?->nama_lengkap} telah {$statusText} oleh " . auth()->user()->name . ".",
                    route('satpam.laporan.index')
                );
            } catch (\Throwable $e) {}
        }

        return redirect()->back()
            ->with('success', 'Laporan satpam berhasil ditindaklanjuti.');
    }
}
