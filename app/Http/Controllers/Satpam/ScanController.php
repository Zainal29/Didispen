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
        return view('satpam.scan', [
            'defaultExpressMode' => filter_var(\App\Models\Setting::get('satpam_express_mode', '0'), FILTER_VALIDATE_BOOLEAN),
            'defaultBeepSound'   => filter_var(\App\Models\Setting::get('satpam_beep_sound', '1'), FILTER_VALIDATE_BOOLEAN),
            'defaultWakeLock'    => filter_var(\App\Models\Setting::get('satpam_wake_lock', '1'), FILTER_VALIDATE_BOOLEAN),
        ]);
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

        \App\Services\DispensasiService::autoExpireOverdueDispensasi();

        $dispensasi = $scanService->parseQRData($request->qr_data);

        if (! $dispensasi) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau data dispensasi tidak ditemukan.',
            ], 404);
        }

        $dispensasi->refresh();
        $dispensasi->loadMissing(['siswa.kelas.jurusan', 'siswa.user', 'guru', 'anggotaSiswa.kelas.jurusan', 'anggotaSiswa.user', 'petugasKeluar', 'petugasKembali']);

        $today = now('Asia/Jakarta')->toDateString();
        $tglIzin = ($dispensasi->tanggal_izin ?? $dispensasi->created_at)?->toDateString() ?? $today;

        // Validasi Tanggal Izin & Status Kadaluarsa
        if ($tglIzin > $today) {
            return response()->json([
                'success' => false,
                'message' => 'Dispensasi ini dijadwalkan untuk tanggal ' . \Carbon\Carbon::parse($tglIzin)->translatedFormat('l, d F Y') . '. Belum dapat digunakan hari ini.',
            ], 422);
        }

        if ($dispensasi->status === 'kadaluarsa') {
            return response()->json([
                'success' => false,
                'message' => 'Dispensasi ini sudah KADALUARSA: ' . ($dispensasi->catatan_admin ?: 'Batas waktu izin telah berakhir.') . ' dan tidak dapat diproses lagi.',
            ], 422);
        }

        if ($tglIzin < $today) {
            return response()->json([
                'success' => false,
                'message' => 'Dispensasi ini sudah KADALUARSA (berlaku tanggal ' . \Carbon\Carbon::parse($tglIzin)->translatedFormat('l, d F Y') . ') dan tidak dapat diproses lagi.',
            ], 422);
        }

        $nowJakarta = now('Asia/Jakarta');
        $dispDate = $dispensasi->tanggal_izin
            ? \Carbon\Carbon::parse($dispensasi->tanggal_izin, 'Asia/Jakarta')
            : ($dispensasi->created_at ? $dispensasi->created_at->copy()->setTimezone('Asia/Jakarta') : $nowJakarta);

        if (\App\Helpers\TimeHelper::isKbmHariSelesai($nowJakarta, $dispDate)) {
            if ($dispensasi->status === 'disetujui' && empty($dispensasi->waktu_keluar_aktual)) {
                $dispensasi->update([
                    'status' => 'kadaluarsa',
                    'catatan_admin' => 'Kadaluarsa otomatis: Siswa tidak melakukan scan keluar di pos gerbang hingga jam KBM berakhir.',
                ]);
                \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);
            }

            return response()->json([
                'success' => false,
                'message' => 'Dispensasi ini sudah KADALUARSA karena jam pelajaran sekolah hari ini sudah berakhir.',
            ], 422);
        }

        $isSampaiPulang = (bool) ($dispensasi->sampai_pulang || stripos($dispensasi->jam_kembali ?? '', 'pulang') !== false);

        // Cek batas waktu toleransi scan keluar (20 menit dari disetujui atau jam mulai izin)
        if ($dispensasi->status === 'disetujui' && empty($dispensasi->waktu_keluar_aktual)) {
            $batasScanKeluar = $dispensasi->getBatasWaktuScanKeluar();
            if ($batasScanKeluar && $nowJakarta->greaterThan($batasScanKeluar)) {
                $jamFormatted = $batasScanKeluar->format('H:i');
                $dispensasi->update([
                    'status' => 'kadaluarsa',
                    'catatan_admin' => "Kadaluarsa otomatis: Siswa tidak melakukan scan keluar di pos gerbang dalam batas waktu 20 menit setelah disetujui ({$jamFormatted} WIB).",
                ]);
                \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);

                return response()->json([
                    'success' => false,
                    'message' => "Dispensasi ini sudah KADALUARSA karena melewati batas waktu toleransi scan keluar ({$jamFormatted} WIB).",
                ], 422);
            }
        }

        // Cek jika batas waktu kembali sudah lewat sebelum siswa melakukan scan keluar
        if ($dispensasi->status === 'disetujui' && empty($dispensasi->waktu_keluar_aktual) && ! $isSampaiPulang) {
            $deadline = $dispensasi->batas_waktu_kembali
                ?: \App\Helpers\TimeHelper::getBatasWaktuKembali($dispensasi->jam_kembali, $dispDate->dayOfWeek);

            if ($deadline) {
                $deadline = $deadline->copy()->setTimezone('Asia/Jakarta')
                    ->setDate($dispDate->year, $dispDate->month, $dispDate->day);

                if ($nowJakarta->greaterThan($deadline)) {
                    $jamFormatted = $deadline->format('H:i');
                    $dispensasi->update([
                        'status' => 'kadaluarsa',
                        'catatan_admin' => "Kadaluarsa otomatis: Siswa tidak melakukan scan keluar di pos gerbang hingga batas waktu kembali ({$jamFormatted} WIB) terlewati.",
                    ]);
                    \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);

                    return response()->json([
                        'success' => false,
                        'message' => "Dispensasi ini sudah KADALUARSA karena batas waktu kembali ({$jamFormatted} WIB) telah terlewati sebelum siswa keluar sekolah.",
                    ], 422);
                }
            }
        }

        // MODE CHECK (Preview Data untuk Modal Konfirmasi)
        if ($request->action === 'check') {
            $isLate = $dispensasi->isOverdue();
            
            // Jika sampai pulang dan sudah keluar, tidak perlu aksi scan kembali lagi
            $canAction = match ($dispensasi->status) {
                'disetujui' => true,
                'keluar'    => ! $isSampaiPulang,
                default     => false,
            };

            $actionType = match ($dispensasi->status) {
                'disetujui' => 'keluar',
                'keluar'    => $isSampaiPulang ? null : 'kembali',
                default     => null,
            };

            $anggotaFormatted = $dispensasi->anggotaSiswa->map(function ($a) {
                return [
                    'id' => $a->id,
                    'nama_lengkap' => $a->nama_lengkap,
                    'nis' => $a->nis_nip ?? $a->user?->nis_nip ?? '-',
                    'kelas' => $a->kelas?->nama_kelas ?? '-',
                ];
            })->values()->all();

            $statusNote = null;
            if ($dispensasi->status === 'keluar' && $isSampaiPulang) {
                $statusNote = 'Dispensasi ini berizin Sampai Pulang Sekolah. Siswa/rombongan tidak perlu melakukan scan kembali.';
            }

            return response()->json([
                'success' => true,
                'mode' => 'preview',
                'data' => [
                    'id' => $dispensasi->id,
                    'nomor_surat' => $dispensasi->nomor_surat,
                    'tipe_pengajuan' => $dispensasi->tipe_pengajuan,
                    'nama_organisasi' => null,
                    'tanggal_izin' => $tglIzin,
                    'status' => $dispensasi->status,
                    'status_note' => $statusNote,
                    'can_action' => $canAction,
                    'action_type' => $actionType,
                    'is_sampai_pulang' => $isSampaiPulang,
                    'is_terlambat' => $isLate,
                    'jam_keluar' => $dispensasi->jam_keluar,
                    'jam_kembali' => $dispensasi->jam_kembali,
                    'waktu_keluar_aktual' => $dispensasi->waktu_keluar_aktual ? \Carbon\Carbon::parse($dispensasi->waktu_keluar_aktual)->timezone('Asia/Jakarta')->format('H:i') : null,
                    'waktu_kembali_aktual' => $dispensasi->waktu_kembali_aktual ? \Carbon\Carbon::parse($dispensasi->waktu_kembali_aktual)->timezone('Asia/Jakarta')->format('H:i') : null,
                    'riwayat_scan' => $dispensasi->riwayat_scan,
                    'alasan' => $dispensasi->alasan,
                    'tujuan' => $dispensasi->tujuan,
                    'lokasi' => $dispensasi->lokasi,
                    'foto_verifikasi' => $dispensasi->foto_verifikasi ? Storage::url($dispensasi->foto_verifikasi) : null,
                    'guru' => $dispensasi->guru?->nama_lengkap ?? '-',
                    'siswa' => [
                        'nama_lengkap' => $dispensasi->siswa?->nama_lengkap ?? 'Tidak Diketahui',
                        'nis' => $dispensasi->siswa?->user?->nis_nip ?? '-',
                        'kelas' => $dispensasi->siswa?->kelas?->nama_kelas ?? '-',
                        'jurusan' => $dispensasi->siswa?->kelas?->jurusan?->nama_jurusan ?? '-',
                    ],
                    'anggota' => $anggotaFormatted,
                    'total_rombongan' => count($anggotaFormatted) + 1,
                ],
            ]);
        }

        // Cek cooldown scan untuk mencegah double scan
        $cooldown = $scanService->checkScanCooldown($dispensasi);
        if ($cooldown['cooldown']) {
            return response()->json([
                'success' => false,
                'message' => $cooldown['message'],
            ], 429);
        }

        // MODE EKSEKUSI (Konfirmasi Keluar / Kembali)
        try {
            if ($dispensasi->status === 'disetujui') {
                $result = $scanService->processKeluar(
                    $dispensasi,
                    auth()->id()
                );

                if ($result['success'] ?? false) {
                    $pesanNotif = $isSampaiPulang
                        ? "Dispensasi Anda ({$dispensasi->nomor_surat}) telah diverifikasi KELUAR (Izin Sampai Pulang Sekolah)."
                        : "Dispensasi Anda ({$dispensasi->nomor_surat}) telah diverifikasi KELUAR. Batas kembali: {$dispensasi->jam_kembali}.";

                    // Notifikasi ke siswa utama
                    if ($dispensasi->siswa?->user_id) {
                        try {
                            $this->notifikasiService->send(
                                $dispensasi->siswa->user_id,
                                $pesanNotif,
                                route('siswa.pengajuan.show', $dispensasi->id)
                            );
                        } catch (\Throwable $e) {}
                    }

                    // Notifikasi ke seluruh anggota rombongan jika organisasi
                    foreach ($dispensasi->anggotaSiswa as $anggota) {
                        if ($anggota->user_id) {
                            try {
                                $this->notifikasiService->send(
                                    $anggota->user_id,
                                    "Dispensasi Rombongan ({$dispensasi->nomor_surat}) telah diverifikasi KELUAR oleh Satpam.",
                                    route('siswa.pengajuan.show', $dispensasi->id)
                                );
                            } catch (\Throwable $e) {}
                        }
                    }
                }

                return response()->json(
                    $result,
                    $result['status_code'] ?? 200
                );
            }

            if ($dispensasi->status === 'keluar') {
                if ($isSampaiPulang) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dispensasi ini berizin Sampai Pulang Sekolah. Tidak perlu scan kembali saat kepulangan.',
                    ], 400);
                }

                $result = $scanService->processKembali(
                    $dispensasi,
                    auth()->id()
                );

                if (($result['success'] ?? false) && $dispensasi->siswa?->user_id) {
                    try {
                        $this->notifikasiService->send(
                            $dispensasi->siswa->user_id,
                            "Dispensasi Anda ({$dispensasi->nomor_surat}) telah SELESAI. Anda telah kembali ke sekolah dengan selamat.",
                            route('siswa.pengajuan.show', $dispensasi->id)
                        );
                    } catch (\Throwable $ne) {
                        \Illuminate\Support\Facades\Log::warning('Gagal kirim notifikasi scan selesai: ' . $ne->getMessage());
                    }

                    foreach ($dispensasi->anggotaSiswa as $anggota) {
                        if ($anggota->user_id) {
                            try {
                                $this->notifikasiService->send(
                                    $anggota->user_id,
                                    "Dispensasi Rombongan ({$dispensasi->nomor_surat}) telah SELESAI. Rombongan telah kembali ke sekolah.",
                                    route('siswa.pengajuan.show', $dispensasi->id)
                                );
                            } catch (\Throwable $e) {}
                        }
                    }
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Satpam ScanController verify error: ' . $e->getMessage(), [
                'exception' => $e,
                'dispensasi_id' => $dispensasi->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses: ' . $e->getMessage(),
            ], 500);
        }
    }
}
