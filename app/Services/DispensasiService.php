<?php
namespace App\Services;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DispensasiService
{
    public function __construct(
        private NotifikasiService $notifikasiService,
        private AuditLogService $auditLogService
    ) {}

    /** Generate nomor surat: DISP-YYYY-NNNN */
    public function generateNomorSurat(): string
    {
        $year = now()->year;
        $last = Dispensasi::whereYear('created_at', $year)->max('id') ?? 0;
        return sprintf('DISP-%d-%04d', $year, $last + 1);
    }

    /** Buat dispensasi baru — ✅ guru_id NULL, terisi saat guru approve */
    public function create(array $data, Siswa $siswa): Dispensasi
    {
        return Dispensasi::create([
            'nomor_surat' => $this->generateNomorSurat(),
            'siswa_id' => $siswa->id,
            'guru_id' => null, // ✅ Terisi saat disetujui guru
            'kategori' => $data['kategori'],
            'alasan' => $data['alasan'],
            'tujuan' => $data['tujuan'],
            'lokasi' => $data['lokasi'] ?? null,
            'jam_keluar' => $data['jam_keluar'],
            'jam_kembali' => $data['jam_kembali'],
            'status' => 'menunggu',
            'max_print_limit' => (int) Setting::get('print_max_limit', 3),
        ]);
    }

    /** Approve dispensasi */
    public function approve(Dispensasi $dispensasi, Guru $guru, ?string $catatan = null): void
    {
        $token = Str::uuid()->toString();
        $dispensasi->update([
            'status' => 'disetujui',
            'guru_id' => $guru->id, // ✅ Catat guru penanggung jawab
            'catatan_admin' => $catatan,
            'verification_token' => $token,
        ]);

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            "✅ Pengajuan Anda ({$dispensasi->nomor_surat}) telah DISETUJUI.",
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($guru->user_id, 'approve', 'dispensasi', $dispensasi->id, null, [
            'status' => 'disetujui', 'token' => $token,
        ]);
    }

    /** Reject dispensasi */
    public function reject(Dispensasi $dispensasi, Guru $guru, string $catatan): void
    {
        $dispensasi->update([
            'status' => 'ditolak',
            'guru_id' => $guru->id, // ✅ Catat guru yang menolak
            'catatan_admin' => $catatan,
        ]);

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            "❌ Pengajuan Anda ({$dispensasi->nomor_surat}) DITOLAK. Alasan: {$catatan}",
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($guru->user_id, 'reject', 'dispensasi', $dispensasi->id, null, [
            'status' => 'ditolak', 'catatan' => $catatan,
        ]);
    }

    /** Konfirmasi siswa keluar */
    public function konfirmasiKeluar(Dispensasi $dispensasi, Guru $guru): void
    {
        $dispensasi->update([
            'status' => 'keluar',
            'waktu_konfirmasi' => now(),
        ]);

        $this->auditLogService->log($guru->user_id, 'konfirmasi_keluar', 'dispensasi', $dispensasi->id);
    }

    /** Konfirmasi siswa kembali */
    public function konfirmasiKembali(Dispensasi $dispensasi, Guru $guru): void
    {
        $dispensasi->update(['status' => 'selesai']);

        $this->notifikasiService->send(
            $dispensasi->siswa->user_id,
            "🏁 Dispensasi ({$dispensasi->nomor_surat}) telah SELESAI.",
            route('siswa.pengajuan.show', $dispensasi->id, false)
        );

        $this->auditLogService->log($guru->user_id, 'konfirmasi_kembali', 'dispensasi', $dispensasi->id);
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

