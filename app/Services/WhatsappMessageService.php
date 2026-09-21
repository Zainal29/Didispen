<?php

namespace App\Services;

use App\Models\Dispensasi;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Str;

class WhatsappMessageService
{
    /**
     * Cache template agar tidak query DB berkali-kali dalam 1 request
     */
    private static ?array $templateCache = null;

    /**
     * Ambil semua template aktif dari DB (dengan cache)
     */
    private function getTemplates(): array
    {
        if (self::$templateCache === null) {
            self::$templateCache = WhatsappTemplate::where('is_active', true)
                ->pluck('content', 'slug')
                ->toArray();
        }
        return self::$templateCache;
    }

    /**
     * Generate link WhatsApp dari template database
     *
     * @param Dispensasi $dispensasi
     * @param string $context 'keluar' | 'terlambat' | 'kembali' | 'disetujui' | 'ditolak'
     * @return string|null  URL wa.me atau null jika tidak valid
     */
    public function generateWaLink(Dispensasi $dispensasi, string $context): ?string
    {
        // 1. Validasi nomor HP
        $noTelepon = $dispensasi->siswa->no_telepon ?? null;
        if (empty($noTelepon)) {
            return null;
        }

        // 2. Format nomor: 08xx → 628xx
        $hp = preg_replace('/[^0-9]/', '', $noTelepon);
        $hp = str_starts_with($hp, '0') ? '62' . substr($hp, 1) : $hp;

        // 3. Ambil template dari DB
        $templates = $this->getTemplates();
        $content = $templates[$context] ?? null;

        if (!$content) {
            // Fallback jika template belum ada di DB
            $content = $this->getDefaultContent($context);
        }

        // 4. Render pesan (replace variabel)
        $pesan = $this->renderContent($content, $dispensasi, $context);

        // 5. Generate URL
        return "https://wa.me/{$hp}?text=" . urlencode($pesan);
    }

    /**
     * Replace variabel {xxx} dengan data asli
     */
    private function renderContent(string $content, Dispensasi $dispensasi, string $context): string
    {
        $siswa = $dispensasi->siswa;
        $kelas = $siswa?->kelas;
        $jurusan = $kelas?->jurusan;

        // Hitung keterlambatan
        $isOverdue = $dispensasi->batas_waktu_kembali
            && now()->greaterThan($dispensasi->batas_waktu_kembali);

        $lateMinutes = 0;
        $lateText = '0 menit';
        if ($isOverdue && $dispensasi->batas_waktu_kembali) {
            $lateMinutes = \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat(
                $dispensasi->batas_waktu_kembali
            );
            $lateText = \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat(
                $lateMinutes,
                short: true
            );
        }

        // Mapping variabel → nilai
        $replacements = [
            '{nama_siswa}'       => $siswa?->nama_lengkap ?? 'Siswa',
            '{nama_kelas}'       => $kelas?->nama_kelas ?? '-',
            '{jurusan}'          => $jurusan?->nama_jurusan ?? '-',
            '{nis}'              => $siswa?->user?->nis_nip ?? '-',
            '{nomor_surat}'      => $dispensasi->nomor_surat ?? '-',
            '{tujuan}'           => $dispensasi->tujuan ?? $dispensasi->lokasi ?? '-',
            '{alasan}'           => $dispensasi->alasan ?? '-',
            '{kategori}'         => Str::headline($dispensasi->kategori ?? '-'),
            '{jam_keluar}'       => $dispensasi->jam_keluar ?? '-',
            '{jam_kembali}'      => $dispensasi->jam_kembali ?? '-',
            '{waktu_aktual}'     => now()->format('H:i'),
            '{durasi_terlambat}' => $lateText,
            '{catatan}'          => $dispensasi->catatan_guru ?? '-',
            '{tanggal}'          => $dispensasi->created_at?->format('d F Y') ?? now()->format('d F Y'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Fallback jika template belum di-seed ke database
     */
    private function getDefaultContent(string $context): string
    {
        return match ($context) {
            'terlambat' => "⚠️ *PERINGATAN DISPENSASI*\n\nYth. *{nama_siswa}*,\n\nAnda telah melewati batas waktu kembali dispensasi (Terlambat *{durasi_terlambat}*).\n\nNo. Surat: {nomor_surat}\nBatas Kembali: {jam_kembali}\n\nSegera kembali ke sekolah atau lapor ke Guru Piket.\n- Sistem DIDISPEN",
            'keluar'    => "Halo *{nama_siswa}*,\n\nAnda telah tercatat *KELUAR* dari sekolah pada pukul {waktu_aktual} untuk dispensasi No: *{nomor_surat}*.\n\nTujuan: {tujuan}\nBatas Kembali: {jam_kembali}\n\nJangan lupa kembali tepat waktu.\n- Sistem DIDISPEN",
            'kembali'   => "Halo *{nama_siswa}*,\n\nDispensasi Anda (No: *{nomor_surat}*) telah *SELESAI*.\n\nTerima kasih sudah kembali ke sekolah tepat waktu.\n- Sistem DIDISPEN",
            'disetujui' => "Halo *{nama_siswa}*,\n\nPengajuan dispensasi Anda dengan nomor surat *{nomor_surat}* telah *DISETUJUI* oleh Guru Piket.\n\nSilakan tunjukkan QR Code kepada Satpam saat keluar.\n\nTerima kasih.\n- Sistem DIDISPEN",
            'ditolak'   => "Halo *{nama_siswa}*,\n\nMohon maaf, pengajuan dispensasi Anda (No: *{nomor_surat}*) *DITOLAK*.\n\nAlasan: {catatan}\n\nSilakan ajukan kembali dengan alasan yang lebih jelas.\n- Sistem DIDISPEN",
            default     => "Halo *{nama_siswa}*, dispensasi No: *{nomor_surat}*. Hubungi Pos Satpam untuk info lebih lanjut.",
        };
    }

    /**
     * Tentukan context berdasarkan status dispensasi
     */
    public function resolveContext(Dispensasi $dispensasi): string
    {
        if ($dispensasi->status === 'keluar') {
            $isOverdue = $dispensasi->batas_waktu_kembali
                && now()->greaterThan($dispensasi->batas_waktu_kembali);
            return $isOverdue ? 'terlambat' : 'keluar';
        }

        return $dispensasi->status ?? 'keluar';
    }

    /**
     * Clear cache (untuk testing atau setelah admin update template)
     */
    public static function clearCache(): void
    {
        self::$templateCache = null;
    }
}
