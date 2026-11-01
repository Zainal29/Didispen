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
    public function index(Request $request, GuruPiketService $guruPiketService)
    {
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $today = now('Asia/Jakarta')->format('Y-m-d');

        // Informasi Guru Piket
        $infoPiket = $guruPiketService->getInformasiSesi();
        $adaJadwalHariIni = false;
        if (empty($infoPiket['jadwal'])) {
            $adaJadwalHariIni = $guruPiketService->getJadwalUntukTanggal()->isNotEmpty();
        }

        // Baca parameter filter dari URL (default: 'semua')
        $filter = $request->get('filter', 'semua');

        // Query seluruh permohonan hari ini dalam satu query efisien
        $semuaDispensasi = Dispensasi::with([
                'siswa.user',
                'siswa.kelas.jurusan',
                'guru',
                'anggotaSiswa.kelas.jurusan',
                'anggotaSiswa.user',
            ])
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                  ->orWhereDate('tanggal_izin', $today);
            })
            ->latest()
            ->get();

        $menungguKeluar = $semuaDispensasi->where('status', 'disetujui')->values();
        $siswaKeluar    = $semuaDispensasi->where('status', 'keluar')->values();
        $selesai        = $semuaDispensasi->where('status', 'selesai')->values();
        $kadaluarsa     = $semuaDispensasi->where('status', 'kadaluarsa')->values();

        // Siswa terlambat:
        // 1. Sedang keluar dan melewati batas waktu kembali (isOverdue)
        // 2. Sempat keluar tetapi tidak scan kembali / kadaluarsa tidak kembali (isNotReturned)
        // 3. Selesai tetapi kembali melewati batas jam kembali (isReturnedLate)
        $siswaTerlambat = $semuaDispensasi->filter(function ($d) {
            return $d->isTerlambat();
        })->values();

        $stats = [
            'total'           => $semuaDispensasi->count(),
            'menunggu_keluar' => $menungguKeluar->count(),
            'keluar'          => $siswaKeluar->count(),
            'terlambat'       => $siswaTerlambat->count(),
            'selesai'         => $selesai->count(),
        ];

        $dihubungi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guru'])
            ->where('is_warned', true)
            ->whereDate('warned_at', today())
            ->latest('warned_at')
            ->limit(50)
            ->get();

        $manualGuruMenunggu = $menungguKeluar->where('dibuat_manual_oleh_guru', true)->values();

        return view('satpam.dashboard', compact(
            'stats',
            'semuaDispensasi',
            'menungguKeluar',
            'siswaKeluar',
            'siswaTerlambat',
            'selesai',
            'kadaluarsa',
            'dihubungi',
            'filter',
            'infoPiket',
            'adaJadwalHariIni',
            'manualGuruMenunggu'
        ));
    }

    /**
     * Ambil data dispensasi yang dibuatkan manual oleh guru piket dan menunggu verifikasi keluar.
     */
    public function getPendingManualGuru()
    {
        $today = now('Asia/Jakarta')->format('Y-m-d');
        $items = Dispensasi::with([
                'siswa.kelas.jurusan',
                'siswa.user',
                'guru',
                'anggotaSiswa.kelas.jurusan',
                'anggotaSiswa.user',
            ])
            ->where('dibuat_manual_oleh_guru', true)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($today) {
                $q->whereDate('created_at', $today)
                  ->orWhereDate('tanggal_izin', $today);
            })
            ->latest()
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'nomor_surat' => $d->nomor_surat,
                    'siswa_nama' => $d->siswa?->nama_lengkap ?? 'Tidak Diketahui',
                    'siswa_nis' => $d->siswa?->user?->nis_nip ?? '-',
                    'siswa_kelas' => $d->siswa?->kelas?->nama_kelas ?? '-',
                    'siswa_jurusan' => $d->siswa?->kelas?->jurusan?->nama_jurusan ?? '-',
                    'foto_verifikasi' => $d->foto_verifikasi ? Storage::url($d->foto_verifikasi) : null,
                    'guru_nama' => $d->guru?->nama_lengkap ?? 'Guru Piket',
                    'jam_keluar' => $d->jam_keluar,
                    'jam_kembali' => $d->jam_kembali,
                    'is_sampai_pulang' => (bool) $d->sampai_pulang,
                    'alasan' => $d->alasan,
                    'tujuan' => $d->tujuan,
                    'lokasi' => $d->lokasi,
                    'detail_url' => route('satpam.dispensasi.detail', $d->id),
                    'tipe_pengajuan' => $d->tipe_pengajuan,
                    'total_rombongan' => $d->isRombongan() ? ($d->anggotaSiswa->count() + 1) : 1,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'data' => $items,
        ]);
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

            $dispensasi = Dispensasi::with([
                    'siswa.user',
                    'siswa.kelas.jurusan',
                    'anggotaSiswa.kelas',
                    'guru',
                    'petugasKeluar',
                    'petugasKembali',
                ])
                ->whereIn('status', ['disetujui', 'keluar', 'selesai', 'kadaluarsa'])
                ->where(function($q) use ($query) {
                    $q->where('nomor_surat', 'like', "%{$query}%")
                      ->orWhere('id', $query)
                      ->orWhereHas('siswa', function($q2) use ($query) {
                          $q2->where('nama_lengkap', 'like', "%{$query}%")
                             ->orWhereHas('user', function($q3) use ($query) {
                                 $q3->where('nis_nip', 'like', "%{$query}%");
                             });
                      })
                      ->orWhereHas('anggotaSiswa', function($q4) use ($query) {
                          $q4->where('nama_lengkap', 'like', "%{$query}%")
                             ->orWhereHas('user', function($q5) use ($query) {
                                 $q5->where('nis_nip', 'like', "%{$query}%");
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
                $isRombongan = $d->isRombongan();
                $totalSiswa = $isRombongan ? ($d->anggotaSiswa->count() + 1) : 1;

                $anggotaList = $d->anggotaSiswa->map(fn($a) => [
                    'nama' => $a->nama_lengkap,
                    'kelas' => $a->kelas?->nama_kelas ?? '-',
                    'nis' => $a->user?->nis_nip ?? '-',
                ])->all();

                return [
                    'id' => $d->id,
                    'nomor_surat' => $d->nomor_surat,
                    'status' => $d->status,
                    'tipe_pengajuan' => $d->tipe_pengajuan ?? ($isRombongan ? 'rombongan' : 'individu'),
                    'dibuat_manual_oleh_guru' => (bool)$d->dibuat_manual_oleh_guru,
                    'is_organisasi' => $isRombongan,
                    'nama_organisasi' => $isRombongan ? 'Rombongan' : null,
                    'total_rombongan' => $totalSiswa,
                    'pembinas' => [],
                    'pembina_text' => null,
                    'guru_piket' => $d->guru?->nama_lengkap ?? null,
                    'siswa_nama' => $d->siswa?->nama_lengkap ?? 'Tidak Diketahui',
                    'siswa_nis' => $d->siswa?->user?->nis_nip ?? '-',
                    'siswa_kelas' => $d->siswa?->kelas?->nama_kelas ?? '-',
                    'siswa_jurusan' => $d->siswa?->kelas?->jurusan?->nama_jurusan ?? '-',
                    'anggota_list' => $anggotaList,
                    'jam_keluar' => $d->jam_keluar,
                    'jam_kembali' => $d->jam_kembali,
                    'is_sampai_pulang' => (bool)$d->sampai_pulang,
                    'waktu_keluar_aktual' => $d->waktu_keluar_aktual ? \Carbon\Carbon::parse($d->waktu_keluar_aktual)->format('H:i') : null,
                    'waktu_kembali_aktual' => $d->waktu_kembali_aktual ? \Carbon\Carbon::parse($d->waktu_kembali_aktual)->format('H:i') : null,
                    'riwayat_scan' => $d->riwayat_scan,
                    'alasan' => $d->alasan,
                    'tujuan' => $d->tujuan,
                    'detail_url' => route('satpam.dispensasi.detail', $d->id),
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
        \App\Services\DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();

        $dispensasi->load([
            'siswa.kelas.jurusan',
            'siswa.user',
            'guru',
            'anggotaSiswa.kelas.jurusan',
            'anggotaSiswa.user',
        ]);

        // ✅ HANYA 1 BARIS untuk generate link WA
        $context = $waService->resolveContext($dispensasi);
        $waLink = $waService->generateWaLink($dispensasi, $context);

        return view('satpam.dispensasi-detail', compact('dispensasi', 'waLink'));
    }

    public function markWaContacted(Dispensasi $dispensasi)
    {
        if (!in_array($dispensasi->status, ['keluar', 'kadaluarsa'])) {
            return response()->json(['success' => false, 'message' => 'Dispensasi tidak valid']);
        }

        $dispensasi->markAsWarned('satpam');

        return response()->json(['success' => true]);
    }
}
