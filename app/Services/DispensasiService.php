<?php

namespace App\Services;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Setting;
use App\Models\WhatsappTemplate; // <--- TAMBAHKAN INI
use Carbon\Carbon;
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
                ->lockForUpdate() // ✅ MENCEGAH DUPLIKAT SAAT REQUEST SERENTAK
                ->count();
            return sprintf('DISP-%d-%04d', $year, $count + 1);
        });
    }

    public function create(array $data, Siswa $siswa): Dispensasi
    {
        return \DB::transaction(function () use ($data, $siswa) {
            $year = now()->year;

            // ✅ AMAN: Menghitung jumlah dispensasi tahun ini untuk reset nomor urut per tahun
            $count = Dispensasi::whereYear('created_at', $year)->count();
            $nomorSurat = sprintf('DISP-%d-%04d', $year, $count + 1);

            return Dispensasi::create(array_merge($data, [
                'nomor_surat' => $nomorSurat,
                'siswa_id' => $siswa->id,
                'guru_id' => null,
                'status' => 'menunggu',
                // ✅ PASTIKAN KEY INI SAMA DENGAN DI SettingsController
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
            'qr_token' => $token, // ✅ GANTI dari 'verification_token'
// Pastikan kolom ini ada di migration dispensasi, atau hapus baris ini jika tidak dipakai
        ]);

        // ✅ GUNAKAN TEMPLATE
        $template = WhatsappTemplate::where('slug', 'disetujui')->where('is_active', true)->first();
        if ($template) {
            $message = $template->render([
                'nama_siswa' => $dispensasi->siswa->nama_lengkap,
                'nomor_surat' => $dispensasi->nomor_surat,
            ]);
        } else {
            $message = "Pengajuan Anda ({$dispensasi->nomor_surat}) telah DISETUJUI."; // Fallback
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

        // ✅ GUNAKAN TEMPLATE
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

    public function konfirmasiKeluar(Dispensasi $dispensasi, $satpamId): void // Sesuaikan parameter dengan controller Anda
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

    public function konfirmasiKembali(Dispensasi $dispensasi, $satpamId): void
    {
        $isLate = $dispensasi->batas_waktu_kembali && now()->greaterThan($dispensasi->batas_waktu_kembali);
        $slug = $isLate ? 'terlambat' : 'kembali';

        $dispensasi->update([
            'status' => 'selesai',
            'waktu_kembali_aktual' => now(),
            'satpam_kembali_id' => $satpamId,
            'is_warned' => $isLate ? true : $dispensasi->is_warned,
            'warned_at' => $isLate ? now() : $dispensasi->warned_at,
        ]);

        // ✅ GUNAKAN TEMPLATE (Otomatis pilih 'terlambat' atau 'kembali')
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
        $end = Setting::get('print_end_time', '17:00');
        $now = now()->format('H:i');

        if ($now < $start || $now > $end) {
            return ['allowed' => false, 'reason' => "Cetak hanya diperbolehkan pukul {$start} - {$end}."];
        }

        return ['allowed' => true];
    }

    /** Lakukan cetak */
    public function doPrint(Dispensasi $dispensasi): void
    {
        $dispensasi->increment('print_count');
        $dispensasi->update(['printed_at' => now()]);
    }
}
