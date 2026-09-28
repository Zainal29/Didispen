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
     * Format nomor HP Indonesia menjadi format internasional WhatsApp (628...)
     */
    public static function formatPhoneNumber(?string $noTelepon): ?string
    {
        if (empty($noTelepon)) {
            return null;
        }

        $hp = preg_replace('/[^0-9]/', '', $noTelepon);
        if (empty($hp)) {
            return null;
        }

        if (str_starts_with($hp, '0')) {
            $hp = '62' . substr($hp, 1);
        } elseif (str_starts_with($hp, '8')) {
            $hp = '62' . $hp;
        }

        return $hp;
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
        // 1. Validasi dan format nomor HP
        $hp = self::formatPhoneNumber($dispensasi->siswa->no_telepon ?? null);
        if (!$hp) {
            return null;
        }

        // 2. Ambil template dari DB
        $templates = $this->getTemplates();
        $content = $templates[$context] ?? null;

        if (!$content) {
            // Fallback jika template belum ada di DB
            $content = $this->getDefaultContent($context);
        }

        // 3. Render pesan (replace variabel)
        $pesan = $this->renderContent($content, $dispensasi, $context);

        // 4. Generate URL
        return "https://wa.me/{$hp}?text=" . urlencode($pesan);
    }

    /**
     * Generate link WhatsApp untuk pengingat jadwal piket guru
     *
     * @param mixed $guru Guru model atau objek dengan nama_lengkap & no_telepon
     * @param \App\Models\JadwalPiket $jadwal
     * @param string|null $templateSlug
     * @return string|null
     */
    public function generatePiketWaLink($guru, \App\Models\JadwalPiket $jadwal, ?string $templateSlug = null): ?string
    {
        if (!$guru) {
            return null;
        }

        $noTelepon = $guru->no_telepon ?? $guru->no_hp ?? null;
        $hp = self::formatPhoneNumber($noTelepon);
        if (!$hp) {
            return null;
        }

        $templates = $this->getTemplates();

        $content = null;
        if ($templateSlug && isset($templates[$templateSlug])) {
            $content = $templates[$templateSlug];
        } else {
            // Cek variasi slug piket
            $content = $templates['pengingat-piket']
                ?? $templates['pengingat_piket']
                ?? $templates['pengingat-jadwal-guru-piket']
                ?? null;

            if (!$content) {
                // Cari template manapun yang memiliki kata 'piket' di slug
                foreach ($templates as $slug => $text) {
                    if (str_contains($slug, 'piket')) {
                        $content = $text;
                        break;
                    }
                }
            }
        }

        if (!$content) {
            $content = "Halo Yth. Bapak/Ibu *{nama_guru}*,\n\nKami mengingatkan bahwa Anda memiliki jadwal piket di sekolah pada:\n📅 Hari: *{hari}*\n⏰ Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n👤 Koordinator: {koordinator}\n\nMohon untuk hadir tepat waktu dan bertugas di pos piket untuk memantau kehadiran serta dispensasi siswa.\n\nTerima kasih atas dedikasi dan kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri";
        }

        $pesan = $this->renderPiketContent($content, $guru, $jadwal);

        return "https://wa.me/{$hp}?text=" . urlencode($pesan);
    }

    /**
     * Render pesan pengingat jadwal piket
     */
    public function renderPiketContent(string $content, $guru, \App\Models\JadwalPiket $jadwal): string
    {
        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $hariStr = is_numeric($jadwal->hari) ? ($namaHari[(int)$jadwal->hari] ?? 'Hari '.$jadwal->hari) : (string)$jadwal->hari;

        $jamMulai = $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-';
        $jamSelesai = $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-';
        $koordinatorNama = $jadwal->koordinator?->nama_lengkap ?? '-';

        $replacements = [
            '{nama_guru}'   => $guru->nama_lengkap ?? $guru->nama ?? 'Bapak/Ibu Guru',
            '{hari}'        => $hariStr,
            '{nama_sesi}'   => $jadwal->nama_sesi ?? 'Piket',
            '{jam_mulai}'   => $jamMulai,
            '{jam_selesai}' => $jamSelesai,
            '{koordinator}' => $koordinatorNama,
            '{tanggal}'     => now()->translatedFormat('d F Y'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
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
