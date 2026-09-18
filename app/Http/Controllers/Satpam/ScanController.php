<?php

namespace App\Http\Controllers\Satpam;

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
     * Tampilkan halaman scan QR.
     */
    public function index()
    {
        return view('satpam.scan');
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
                'message' => 'QR Code tidak valid atau data dispensasi tidak ditemukan.',
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
        if ($dispensasi->status === 'disetujui') {
            $result = $scanService->processKeluar(
                $dispensasi,
                auth()->id()
            );

            return response()->json(
                $result,
                $result['status_code'] ?? 200
            );
        }

        if ($dispensasi->status === 'keluar') {
            $result = $scanService->processKembali(
                $dispensasi,
                auth()->id()
            );

            if ($result['success'] ?? false) {
                $this->notifikasiService->send(
                    $dispensasi->siswa->user_id,
                    "Dispensasi Anda ({$dispensasi->nomor_surat}) telah SELESAI. Anda telah kembali ke sekolah dengan selamat.",
                    route('siswa.pengajuan.show', $dispensasi->id)
                );
            }

            return response()->json(
                $result,
                $result['status_code'] ?? 200
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'QR Code ini sudah selesai diproses atau status tidak valid (Status: '
                . ucfirst($dispensasi->status)
                . ').',
            'data' => $dispensasi,
        ], 400);
    }
}
