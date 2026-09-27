<?php

namespace App\Services;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DispensasiService
{
    public function __construct(
        private NotifikasiService $notifikasiService,
        private AuditLogService $auditLogService
    ) {}

    public function generateNomorSurat(): string
    {
        return \DB::transaction(function () {
            $year = now()->year;
            $count = Dispensasi::whereYear('created_at', $year)
                ->lockForUpdate()
                ->count();
            return sprintf('DISP-%d-%04d', $year, $count + 1);
        });
    }

    public function create(array $data, Siswa $siswa): Dispensasi
    {
        return \DB::transaction(function () use ($data, $siswa) {
            $year = now()->year;

            $count = Dispensasi::whereYear('created_at', $year)->count();
            $nomorSurat = sprintf('DISP-%d-%04d', $year, $count + 1);

            return Dispensasi::create(array_merge($data, [
                'nomor_surat' => $nomorSurat,
                'siswa_id' => $siswa->id,
                'guru_id' => null,
                'status' => 'menunggu',
                'max_print_limit' => (int) Setting::get('student_print_limit', 3),
            ]));
        });
    }

    public function approve(Dispensasi $dispensasi, Guru $guru, ?string $catatan = null): void
    {
        $token = Str::uuid()->toString();
        $dispensasi->update([
            'status' => 'disetujui',
            'guru_id' => $guru->id,
            'catatan_admin' => $catatan,
            'qr_token' => $token,
        ]);

        $template = WhatsappTemplate::where('slug', 'disetujui')->where('is_active', true)->first();
        if ($template) {
            $message = $template->render([
                'nama_siswa' => $dispensasi->siswa->nama_lengkap,
                'nomor_surat' => $dispensasi->nomor_surat,
            ]);
        } else {
            $message = "Pengajuan Anda ({$dispensasi->nomor_surat}) telah DISETUJUI.";
        }

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            $message,
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($guru->user_id, 'approve', 'dispensasi', $dispensasi->id, null, [
            'status' => 'disetujui', 'token' => $token,
        ]);
    }

    public function reject(Dispensasi $dispensasi, Guru $guru, string $catatan): void
    {
        $dispensasi->update([
            'status' => 'ditolak',
            'guru_id' => $guru->id,
            'catatan_admin' => $catatan,
        ]);

        $template = WhatsappTemplate::where('slug', 'ditolak')->where('is_active', true)->first();
        if ($template) {
            $message = $template->render([
                'nama_siswa' => $dispensasi->siswa->nama_lengkap,
                'nomor_surat' => $dispensasi->nomor_surat,
                'catatan' => $catatan,
            ]);
        } else {
            $message = "Pengajuan Anda ({$dispensasi->nomor_surat}) DITOLAK. Alasan: {$catatan}";
        }

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            $message,
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($guru->user_id, 'reject', 'dispensasi', $dispensasi->id, null, [
            'status' => 'ditolak', 'catatan' => $catatan,
        ]);
    }

    public function konfirmasiKeluar(Dispensasi $dispensasi, $satpamId): void
    {
        $dispensasi->update([
            'status' => 'keluar',
            'waktu_keluar_aktual' => now(),
            'satpam_keluar_id' => $satpamId,
        ]);

        $template = WhatsappTemplate::where('slug', 'keluar')->where('is_active', true)->first();
        if ($template) {
            $message = $template->render([
                'nama_siswa' => $dispensasi->siswa->nama_lengkap,
                'nomor_surat' => $dispensasi->nomor_surat,
                'waktu_aktual' => now()->format('H:i'),
                'jam_kembali' => $dispensasi->jam_kembali,
            ]);
        } else {
            $message = "Anda telah tercatat KELUAR dari sekolah.";
        }

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            $message,
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($satpamId, 'konfirmasi_keluar', 'dispensasi', $dispensasi->id);
    }

    /**
     * Cleanup file fisik (QR code, foto verifikasi, foto bukti) saat dispensasi selesai.
     * Record database dan histori TETAP DIPERTAHANKAN.
     * Idempotent & aman: hanya dijalankan jika status benar-benar 'selesai'.
     */
    public static function cleanupCompletedDispensasiFiles(Dispensasi $dispensasi): void
    {
        // Guard: Jangan pernah menghapus file jika status bukan 'selesai'
        if ($dispensasi->status !== 'selesai') {
            return;
        }

        // 1. Hapus QR code fisik milik dispensasi ini
        if (!empty($dispensasi->qr_code) && Storage::disk('public')->exists($dispensasi->qr_code)) {
            Storage::disk('public')->delete($dispensasi->qr_code);
        }
        foreach (['qr_codes/dispensasi_'.$dispensasi->id.'.svg', 'qr_codes/dispensasi_'.$dispensasi->id.'.png'] as $qrPath) {
            if (Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }
        }

        // 2. Hapus foto verifikasi fisik
        if (!empty($dispensasi->foto_verifikasi) && Storage::disk('public')->exists($dispensasi->foto_verifikasi)) {
            Storage::disk('public')->delete($dispensasi->foto_verifikasi);
        }

        // 3. Hapus foto bukti fisik
        if (!empty($dispensasi->foto_bukti) && Storage::disk('public')->exists($dispensasi->foto_bukti)) {
            Storage::disk('public')->delete($dispensasi->foto_bukti);
        }
    }

    public function konfirmasiKembali(Dispensasi $dispensasi, $satpamId): void
    {
        if ($dispensasi->status !== 'keluar') {
            throw new \InvalidArgumentException('Dispensasi harus dalam status keluar untuk dikonfirmasi kembali.');
        }

        $isLate = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
        $slug = $isLate ? 'terlambat' : 'kembali';

        // 1. DB transaction + lockForUpdate untuk memastikan update status berhasil sebelum file disentuh
        \Illuminate\Support\Facades\DB::transaction(function () use ($dispensasi, $satpamId, $isLate) {
            $updated = Dispensasi::whereKey($dispensasi->id)
                ->where('status', 'keluar')
                ->lockForUpdate()
                ->update([
                    'status' => 'selesai',
                    'waktu_kembali_aktual' => now(),
                    'satpam_kembali_id' => $satpamId,
                    'is_warned' => $isLate ? true : $dispensasi->is_warned,
                    'warned_at' => $isLate ? now() : $dispensasi->warned_at,
                ]);

            if ($updated !== 1) {
                throw new \RuntimeException('Gagal mengubah status dispensasi menjadi selesai. Status mungkin sudah berubah.');
            }
        });

        // 2. Refresh model setelah transaction commit agar status di memori menjadi 'selesai'
        $dispensasi->refresh();

        // 3. Cleanup file fisik (QR code, foto_verifikasi, foto_bukti) SETELAH commit berhasil
        self::cleanupCompletedDispensasiFiles($dispensasi);

        // GUNAKAN TEMPLATE
        $template = WhatsappTemplate::where('slug', $slug)->where('is_active', true)->first();
        if ($template) {
            $durasi = $isLate ? \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat(
                \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($dispensasi->batas_waktu_kembali)
            ) : '0 menit';

            $message = $template->render([
                'nama_siswa' => $dispensasi->siswa->nama_lengkap,
                'nomor_surat' => $dispensasi->nomor_surat,
                'durasi_terlambat' => $durasi,
                'jam_kembali' => $dispensasi->jam_kembali,
            ]);
        } else {
            $message = "Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI.";
        }

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            $message,
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($satpamId, 'konfirmasi_kembali', 'dispensasi', $dispensasi->id);
    }

    /** Cek apakah boleh cetak */
    public function canPrint(Dispensasi $dispensasi): array
    {
        $printLimit = min(
            (int) $dispensasi->max_print_limit,
            (int) config('app.print_limit', 3)
        );

        if ($dispensasi->print_count >= $printLimit) {
            return ['allowed' => false, 'reason' => 'Batas cetak telah tercapai.'];
        }

        $start = Setting::get('print_start_time', '06:00');
        $end = Setting::get('print_end_time', '18:00');
        $now = now()->format('H:i');

        if ($now < $start || $now > $end) {
            return ['allowed' => false, 'reason' => "Cetak hanya diperbolehkan pukul {$start} - {$end}."];
        }

        return ['allowed' => true, 'reason' => ''];
    }
}
