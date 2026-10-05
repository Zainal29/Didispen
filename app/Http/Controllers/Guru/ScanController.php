<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Services\QRScanService;
use App\Services\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function __construct(
        private NotifikasiService $notifikasiService
    ) {}

    /**
     * Tampilkan halaman scan QR
     */
    public function index()
    {
        return view('guru.scan');
    }

    /**
     * Proses verifikasi / pengecekan QR Code.
     */
    public function verify(Request $request, QRScanService $scanService)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'action'  => 'nullable|string', // 'check', 'keluar', 'kembali', 'confirm'
        ]);

        $dispensasi = $scanService->parseQRData($request->qr_data);

        if (! $dispensasi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak ditemukan atau tidak valid! Pastikan formatnya benar.'
            ], 404);
        }

        $dispensasi->loadMissing(['siswa.kelas.jurusan', 'siswa.user', 'guru']);

        // MODE CHECK (Preview Data untuk Modal Konfirmasi)
        if ($request->action === 'check') {
            $isLate = $dispensasi->isOverdue();
            $canAction = in_array($dispensasi->status, ['disetujui', 'keluar']);
            $actionType = match ($dispensasi->status) {
                'disetujui' => 'keluar',
                'keluar'    => 'kembali',
                default     => null,
            };

            $isSampaiPulang = stripos($dispensasi->jam_kembali ?? '', 'pulang') !== false;

            return response()->json([
                'success' => true,
                'mode' => 'preview',
                'data' => [
                    'id' => $dispensasi->id,
                    'nomor_surat' => $dispensasi->nomor_surat,
                    'status' => $dispensasi->status,
                    'can_action' => $canAction,
                    'action_type' => $actionType,
                    'is_sampai_pulang' => $isSampaiPulang,
                    'is_terlambat' => $isLate,
                    'jam_keluar' => $dispensasi->jam_keluar,
                    'jam_kembali' => $dispensasi->jam_kembali,
                    'alasan' => $dispensasi->alasan,
                    'tujuan' => $dispensasi->tujuan,
                    'foto_verifikasi' => $dispensasi->foto_verifikasi ? Storage::url($dispensasi->foto_verifikasi) : null,
                    'guru' => $dispensasi->guru?->nama_lengkap ?? '-',
                    'siswa' => [
                        'nama_lengkap' => $dispensasi->siswa?->nama_lengkap ?? 'Tidak Diketahui',
                        'nis' => $dispensasi->siswa?->user?->nis_nip ?? '-',
                        'kelas' => $dispensasi->siswa?->kelas?->nama_kelas ?? '-',
                        'jurusan' => $dispensasi->siswa?->kelas?->jurusan?->nama_jurusan ?? '-',
                    ],
                ],
            ]);
        }

        // MODE EKSEKUSI (Konfirmasi Keluar / Kembali)
        try {
            if ($dispensasi->status === 'disetujui') {
                $result = $scanService->processKeluar($dispensasi, auth()->id());
                return response()->json($result, $result['status_code'] ?? 200);
            }

            if ($dispensasi->status === 'keluar') {
                $result = $scanService->processKembali($dispensasi, auth()->id());

                if (($result['success'] ?? false) && $dispensasi->siswa?->user_id) {
                    try {
                        $this->notifikasiService->send(
                            $dispensasi->siswa->user_id,
                            "Dispensasi Anda ({$dispensasi->nomor_surat}) telah SELESAI. Anda telah kembali ke sekolah dengan selamat.",
                            route('siswa.pengajuan.show', $dispensasi->id)
                        );
                    } catch (\Throwable $ne) {
                        \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi guru scan selesai: ' . $ne->getMessage());
                    }
                }

                return response()->json($result, $result['status_code'] ?? 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'QR Code ini sudah selesai diproses atau status tidak valid (Status: ' . ucfirst($dispensasi->status) . ').',
                'data' => $dispensasi,
            ], 400);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Guru ScanController verify error: ' . $e->getMessage(), [
                'exception' => $e,
                'dispensasi_id' => $dispensasi->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pencarian manual dispensasi untuk Guru
     */
    public function searchDispensasi(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:255'
            ]);

            $query = trim($request->input('query'));

            $dispensasi = \App\Models\Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan'])
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

        } catch (\Throwable $e) {
            \Log::error('Guru Search Dispensasi Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Konfirmasi keluar oleh Guru
     */
    public function konfirmasiKeluar(\App\Models\Dispensasi $dispensasi, QRScanService $scanService)
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
            \Log::error('Guru konfirmasi keluar error: ' . $e->getMessage(), ['exception' => $e]);
            $msg = 'Terjadi kesalahan sistem saat konfirmasi keluar: ' . $e->getMessage();
            return request()->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg], 500)
                : redirect()->back()->with('error', $msg);
        }
    }

    /**
     * Konfirmasi kembali oleh Guru
     */
    public function konfirmasiKembali(\App\Models\Dispensasi $dispensasi, \App\Services\DispensasiService $dispensasiService)
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
            \Log::error('Guru konfirmasi kembali error: ' . $e->getMessage(), ['exception' => $e]);
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
}
