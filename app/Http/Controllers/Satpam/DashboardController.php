<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\WhatsappTemplate;
use App\Services\QRScanService;
use App\Services\NotifikasiService;
use App\Services\WhatsappMessageService; // ✅ TAMBAHKAN
use App\Services\GuruPiketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request, GuruPiketService $guruPiketService) // <i class="fas fa-check-circle"></i> 1. TAMBAHKAN Request $request
    {
        $today = now()->format('Y-m-d');

        // Informasi Guru Piket
        $infoPiket = $guruPiketService->getInformasiSesi();
        $adaJadwalHariIni = false;
        if (empty($infoPiket['jadwal'])) {
            $adaJadwalHariIni = $guruPiketService->getJadwalUntukTanggal()->isNotEmpty();
        }

        // <i class="fas fa-check-circle"></i> 2. TAMBAHKAN INI: Baca parameter filter dari URL (default: 'semua')
        $filter = $request->get('filter', 'semua');

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

        $stats = [
            'total' => $menungguKeluar->count() + $siswaKeluar->count() + $selesai->count(),
            'menunggu_keluar' => $menungguKeluar->count(),
            'keluar' => $siswaKeluar->count(),
            'selesai' => $selesai->count(),
        ];

      $dihubungi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
                ->where('is_warned', true)
                ->whereDate('warned_at', today())
                ->latest('warned_at')
                ->limit(50)
                ->get();


        // <i class="fas fa-check-circle"></i> 3. TAMBAHKAN 'filter' ke dalam compact agar bisa dibaca oleh View
        return view('satpam.dashboard', compact('stats', 'menungguKeluar', 'siswaKeluar', 'selesai', 'dihubungi', 'filter', 'infoPiket', 'adaJadwalHariIni'));
    }

    /**
     * <i class="fas fa-check-circle"></i> PENCARIAN MANUAL DISPENSASI (Untuk Verifikasi Satpam)
     */
    /**
     * <i class="fas fa-check-circle"></i> PENCARIAN MANUAL DISPENSASI (Untuk Verifikasi Satpam)
     */
    public function searchDispensasi(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:255'
            ]);

            $query = trim($request->input('query'));

            $dispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
                ->whereIn('status', ['disetujui', 'keluar', 'selesai'])
                ->where(function($q) use ($query) {
                    $q->where('nomor_surat', 'like', "%{$query}%")
                      ->orWhere('id', $query)
                      ->orWhereHas('siswa', function($q2) use ($query) {
                          $q2->where('nama_lengkap', 'like', "%{$query}%")
                             ->orWhereHas('user', function($q3) use ($query) {
                                 $q3->where('nis_nip', 'like', "%{$query}%");
                             });
                      });
                })
                ->latest()
                ->limit(10)
                ->get();

            if ($dispensasi->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dispensasi tidak ditemukan. Pastikan No. Surat, NIS, atau Nama benar.'
                ], 404);
            }

            $results = $dispensasi->map(function($d) {
                return [
                    'id' => $d->id,
                    'nomor_surat' => $d->nomor_surat,
                    'status' => $d->status,
                    'siswa_nama' => $d->siswa?->nama_lengkap ?? 'Tidak Diketahui',
                    'siswa_nis' => $d->siswa?->user?->nis_nip ?? '-',
                    'siswa_kelas' => $d->siswa?->kelas?->nama_kelas ?? '-',
                    'siswa_jurusan' => $d->siswa?->kelas?->jurusan?->nama_jurusan ?? '-',
                    'jam_keluar' => $d->jam_keluar,
                    'jam_kembali' => $d->jam_kembali,
                    'alasan' => $d->alasan,
                    'tujuan' => $d->tujuan,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('Search Dispensasi Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Konfirmasi keluar (Mendukung AJAX & Form)
     */
    public function konfirmasiKeluar(Dispensasi $dispensasi, QRScanService $scanService)
    {
        try {
            $result = $scanService->processKeluar($dispensasi, (int) auth()->id());
            $message = $result['message'];

            if (! $result['success']) {
                return request()->wantsJson()
                    ? response()->json($result, $result['status_code'] ?? 400)
                    : redirect()->back()->with('error', $message);
            }

            return request()->wantsJson()
                ? response()->json($result)
                : redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Konfirmasi keluar manual error: ' . $e->getMessage(), ['exception' => $e]);
            $msg = 'Terjadi kesalahan sistem saat konfirmasi keluar: ' . $e->getMessage();
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Konfirmasi kembali (Mendukung AJAX & Form)
     */
    public function konfirmasiKembali(Dispensasi $dispensasi, \App\Services\DispensasiService $dispensasiService)
    {
        if ($dispensasi->status !== 'keluar') {
            $message = 'Dispensasi harus dalam status keluar untuk dikonfirmasi kembali.';
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 400)
                : redirect()->back()->with('error', $message);
        }

        try {
            $dispensasiService->konfirmasiKembali($dispensasi, (int) auth()->id());
        } catch (\Throwable $e) {
            Log::error('Konfirmasi kembali manual error: ' . $e->getMessage(), ['exception' => $e]);
            $message = $e->getMessage() ?: 'Gagal memproses kembali dispensasi.';
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 500)
                : redirect()->back()->with('error', $message);
        }

        $message = "Siswa " . ($dispensasi->siswa?->nama_lengkap ?? 'Siswa') . " berhasil dikonfirmasi KEMBALI.";

        return request()->wantsJson()
            ? response()->json(['success' => true, 'message' => $message])
            : redirect()->back()->with('success', $message);
    }

    // public function showDetail(Dispensasi $dispensasi)
    // {
    //     $dispensasi->load(['siswa.kelas.jurusan', 'guru']);
    //     return view('satpam.dispensasi-detail', compact('dispensasi'));
    // }
    public function showDetail(Dispensasi $dispensasi, WhatsappMessageService $waService)
       {
           $dispensasi->load(['siswa.kelas.jurusan', 'siswa.user', 'guru']);

           // ✅ HANYA 1 BARIS untuk generate link WA
           $context = $waService->resolveContext($dispensasi);
           $waLink = $waService->generateWaLink($dispensasi, $context);

           return view('satpam.dispensasi-detail', compact('dispensasi', 'waLink'));
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
