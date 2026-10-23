<?php

namespace App\Services;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Str;

class WhatsappMessageService
{
    /**
     * Placeholder valid untuk template Hubungi Guru Piket.
     * Digunakan untuk validasi pada editor admin.
     */
    public const VALID_HUBUNGI_PLACEHOLDERS = [
        'nama_guru', 'nama_siswa', 'nis', 'kelas', 'jurusan',
        'nomor_surat', 'kategori', 'alasan', 'tujuan', 'lokasi',
        'jam_keluar', 'jam_kembali', 'link_dispensasi', 'link_web',
    ];

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

    // 08xxxxxxxxxx → 628xxxxxxxxxx
    if (str_starts_with($hp, '0')) {
        $hp = '62' . substr($hp, 1);
    }
    // 8xxxxxxxxxx → 628xxxxxxxxxx
    elseif (str_starts_with($hp, '8')) {
        $hp = '62' . $hp;
    }
    // 62xxxxxxxxxx → tetap
    elseif (str_starts_with($hp, '62')) {
        // tidak perlu diubah
    }
    // Format negara lain / format tidak dikenal
    else {
        return null;
    }

    // Nomor Indonesia harus berada pada panjang yang wajar.
    // Contoh: 628123456789
    if (! preg_match('/^628[0-9]{8,11}$/', $hp)) {
        return null;
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
            $content = "Halo Yth. Bapak/Ibu *{nama_guru}*,\n\nKami mengingatkan bahwa Anda memiliki jadwal piket di sekolah pada:\nHari: *{hari}*\nSesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\nKoordinator: {koordinator}\n\nMohon untuk hadir tepat waktu dan bertugas di pos piket untuk memantau kehadiran serta dispensasi siswa.\n\nTerima kasih atas dedikasi dan kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri";
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

        $leadMinutes = (int) \App\Models\Setting::get('wa_piket_reminder_minutes', 15);
        $menitPengingat = $leadMinutes > 0 ? "{$leadMinutes} menit" : "sesaat";

        $replacements = [
            '{nama_guru}'       => $guru->nama_lengkap ?? $guru->nama ?? 'Bapak/Ibu Guru',
            '{hari}'            => $hariStr,
            '{nama_sesi}'       => $jadwal->nama_sesi ?? 'Piket',
            '{jam_mulai}'       => $jamMulai,
            '{jam_selesai}'     => $jamSelesai,
            '{koordinator}'     => $koordinatorNama,
            '{menit_pengingat}' => $menitPengingat,
            '{tanggal}'         => now()->translatedFormat('d F Y'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Render pesan pengingat untuk Koordinator Guru Piket
     */
    public function renderKoordinatorPiketContent(string $content, \App\Models\Guru $koordinator, \App\Models\JadwalPiket $jadwal, array $petugasList = []): string
    {
        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $hariStr = is_numeric($jadwal->hari) ? ($namaHari[(int)$jadwal->hari] ?? 'Hari '.$jadwal->hari) : (string)$jadwal->hari;

        $jamMulai = $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-';
        $jamSelesai = $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-';

        $petugasFormatted = [];
        foreach ($petugasList as $p) {
            $guruObj = is_array($p) ? ($p['guru'] ?? null) : $p;
            if ($guruObj instanceof \App\Models\Guru) {
                $nama = $guruObj->nama_lengkap ?? $guruObj->nama ?? 'Guru';
                $status = (is_array($p) && !empty($p['is_pengganti'])) ? ' (Pengganti)' : '';
                $petugasFormatted[] = "• {$nama}{$status}";
            }
        }
        $daftarPetugasText = !empty($petugasFormatted) ? implode("\n", $petugasFormatted) : '• (Belum ada petugas terdaftar)';

        $replacements = [
            '{nama_koordinator}' => $koordinator->nama_lengkap ?? $koordinator->nama ?? 'Bapak/Ibu Koordinator',
            '{hari}'             => $hariStr,
            '{nama_sesi}'        => $jadwal->nama_sesi ?? 'Piket',
            '{jam_mulai}'        => $jamMulai,
            '{jam_selesai}'      => $jamSelesai,
            '{daftar_petugas}'   => $daftarPetugasText,
            '{tanggal}'          => now('Asia/Jakarta')->translatedFormat('d F Y'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Render pesan WA Pengingat Siaga Pos Satpam sesuai Sesi Guru Piket
     */
    private function renderSatpamContent(string $content, \App\Models\JadwalPiket $jadwal, array $petugasList = []): string
    {
        $hariMap = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $hariStr = $hariMap[$jadwal->hari] ?? 'Hari Ini';

        $jamMulai = substr((string) $jadwal->jam_mulai, 0, 5);
        $jamSelesai = substr((string) $jadwal->jam_selesai, 0, 5);

        $petugasFormatted = [];
        foreach ($petugasList as $p) {
            $guruObj = is_array($p) ? ($p['guru'] ?? null) : $p;
            if ($guruObj instanceof \App\Models\Guru) {
                $nama = $guruObj->nama_lengkap ?? $guruObj->nama ?? 'Guru';
                $status = (is_array($p) && !empty($p['is_pengganti'])) ? ' (Pengganti)' : '';
                $petugasFormatted[] = "• {$nama}{$status}";
            }
        }
        $daftarPetugasText = !empty($petugasFormatted) ? implode("\n", $petugasFormatted) : '• (Belum ada petugas terdaftar)';
        $koordinatorName = $jadwal->koordinator?->nama_lengkap ?? $jadwal->koordinator?->nama ?? '-';

        $scanSatpamUrl = rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/') . '/satpam/scan';

        $replacements = [
            '{hari}'             => $hariStr,
            '{nama_sesi}'        => $jadwal->nama_sesi ?? 'Piket',
            '{jam_mulai}'        => $jamMulai,
            '{jam_selesai}'      => $jamSelesai,
            '{daftar_petugas}'   => $daftarPetugasText,
            '{koordinator}'      => $koordinatorName,
            '{nama_koordinator}' => $koordinatorName,
            '{tanggal}'          => now('Asia/Jakarta')->translatedFormat('d F Y'),
            '{link_scan_satpam}' => $scanSatpamUrl,
            '{link_web}'         => rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/'),
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
            '{link_dispensasi}'  => $dispensasi->link_dispensasi ?? '-',
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
            'terlambat' => " *PERINGATAN DISPENSASI*\n\nYth. *{nama_siswa}*,\n\nAnda telah melewati batas waktu kembali dispensasi (Terlambat *{durasi_terlambat}*).\n\nNo. Surat: {nomor_surat}\nBatas Kembali: {jam_kembali}\n\nSegera kembali ke sekolah atau lapor ke Guru Piket.\n- Sistem DIDISPEN",
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

    /**
     * Generate WA link untuk siswa menghubungi Guru Piket.
     *
     * Prioritas Guru Piket:
     *   1. Guru yang tersimpan di pengajuan (guru_id)
     *   2. Guru fallback dari setting admin (fallback_guru_piket_id)
     *
     * Prioritas Template:
     *   1. Template aktif di DB (slug: hubungi-guru-piket)
     *   2. Default template sistem
     *
     * @param  Dispensasi  $dispensasi  Pengajuan milik siswa
     * @param  Guru|null   $guruFallback  Guru fallback (dari resolver jadwal atau setting)
     * @return array{
     *     url: string|null,
     *     guru: Guru|null,
     *     guru_source: string,
     *     template_source: string,
     *     error: string|null,
     * }
     */
    public function generateHubungiGuruPiketWaLink(Dispensasi $dispensasi, ?Guru $guruFallback = null): array
    {
        $dispensasi->loadMissing(['guru', 'siswa.kelas.jurusan', 'siswa.user']);

        // --- 1. Tentukan Guru Piket ---
        $guru       = null;
        $guruSource = 'none';

        // Prioritas 1: Guru yang tersimpan di pengajuan
        if ($dispensasi->guru && $dispensasi->guru->status_aktif) {
            $guru       = $dispensasi->guru;
            $guruSource = 'application';
        }

        // Prioritas 2: Guru fallback (dari jadwal / setting)
        if (! $guru && $guruFallback && $guruFallback->status_aktif) {
            $guru       = $guruFallback;
            $guruSource = ($guruSource === 'none') ? 'fallback' : $guruSource;
        }

        // Tidak ada guru → kembalikan error tanpa URL
        if (! $guru) {
            return [
                'url'             => null,
                'guru'            => null,
                'guru_source'     => 'none',
                'template_source' => 'none',
                'error'           => 'Guru Piket belum tersedia untuk pengajuan ini.',
            ];
        }

        // --- 2. Cari nomor WA guru ---
        $hp = self::formatPhoneNumber($guru->no_telepon ?? null);

        if (! $hp) {
            return [
                'url'             => null,
                'guru'            => $guru,
                'guru_source'     => $guruSource,
                'template_source' => 'none',
                'error'           => 'Nomor WhatsApp Guru Piket belum tersedia.',
            ];
        }

        // --- 3. Ambil template ---
        $templates      = $this->getTemplates();
        $templateSource = 'admin';
        $content        = $templates['hubungi-guru-piket'] ?? null;

        if (empty($content)) {
            $templateSource = 'default';
            $content        = $this->getDefaultHubungiContent();
        }

        // --- 4. Render placeholder ---
        $pesan = $this->renderHubungiGuruPiketContent($content, $dispensasi, $guru);

        // --- 5. Generate URL ---
        return [
            'url'             => 'https://wa.me/' . $hp . '?text=' . urlencode($pesan),
            'guru'            => $guru,
            'guru_source'     => $guruSource,
            'template_source' => $templateSource,
            'error'           => null,
        ];
    }

    /**
     * Render isi template Hubungi Guru Piket dengan data asli.
     */
    public function renderHubungiGuruPiketContent(string $content, Dispensasi $dispensasi, Guru $guru): string
    {
        $dispensasi->loadMissing(['siswa.kelas.jurusan', 'siswa.user']);

        $siswa   = $dispensasi->siswa;
        $kelas   = $siswa?->kelas;
        $jurusan = $kelas?->jurusan;

        // link_dispensasi: route siswa ke detail pengajuan (butuh auth, jangan bikin link publik)
        $linkDispensasi = '-';
        try {
            $linkDispensasi = route('siswa.pengajuan.show', $dispensasi);
        } catch (\Throwable) {
            // route tidak ditemukan, biarkan '-'
        }

        $replacements = [
            '{nama_guru}'      => $guru->nama_lengkap ?? 'Guru Piket',
            '{nama_siswa}'     => $siswa?->nama_lengkap ?? '-',
            '{nis}'            => $siswa?->user?->nis_nip ?? '-',
            '{kelas}'          => $kelas?->nama_kelas ?? '-',
            '{jurusan}'        => $jurusan?->nama_jurusan ?? '-',
            '{nomor_surat}'    => $dispensasi->nomor_surat ?? '-',
            '{kategori}'       => Str::headline(str_replace('_', ' ', $dispensasi->kategori ?? '-')),
            '{alasan}'         => $dispensasi->alasan ?? '-',
            '{tujuan}'         => $dispensasi->tujuan ?? '-',
            '{lokasi}'         => $dispensasi->lokasi ?? '-',
            '{jam_keluar}'     => $dispensasi->jam_keluar ?? '-',
            '{jam_kembali}'    => $dispensasi->jam_kembali ?? '-',
            '{link_dispensasi}' => $linkDispensasi,
            '{link_web}'       => rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/'),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }

    /**
     * Default template jika Admin belum membuat template hubungi-guru-piket.
     */
    private function getDefaultHubungiContent(): string
    {
        return "Halo Pak/Bu {nama_guru},\n\nSaya {nama_siswa} dari kelas {kelas}, jurusan {jurusan}.\n\nSaya ingin menghubungi Bapak/Ibu selaku Guru Piket karena saat ini Bapak/Ibu sedang tidak berada di tempat.\n\nBerikut informasi pengajuan saya:\n\nNama: {nama_siswa}\nNIS: {nis}\nKelas: {kelas}\nJurusan: {jurusan}\n\nNomor Surat: {nomor_surat}\nKeperluan: {kategori}\nAlasan: {alasan}\nTujuan: {tujuan}\nLokasi: {lokasi}\nJam Keluar: {jam_keluar}\nJam Kembali: {jam_kembali}\n\nMohon dapat membantu menindaklanjuti pengajuan saya melalui DIDISPEN.\n\nWebsite DIDISPEN:\n{link_web}\n\nTerima kasih, Pak/Bu.";
    }

    /**
     * =========================================================================
     * INTEGRASI BOT BAILEYS (PENGIRIMAN OTOMATIS VIA QUEUE)
     * =========================================================================
     */

    /**
     * Dispatch pengiriman pesan WhatsApp ke Queue Baileys dengan proteksi duplikasi
     */
    public function dispatchBaileysMessage(
        ?string $rawPhone,
        string $message,
        string $context,
        ?int $userId = null,
        ?string $recipientName = null,
        ?string $referenceKey = null
    ): ?\App\Models\WhatsappLog {
        $botEnabled = (bool) \App\Models\Setting::get('wa_bot_enabled', config('services.whatsapp_bot.enabled', true));
        if (! $botEnabled) {
            return null;
        }

        // Cegah pengiriman ganda jika referenceKey sudah pernah dikirim
        if ($referenceKey && \App\Models\WhatsappLog::where('reference_key', $referenceKey)->whereIn('status', ['pending', 'sent'])->exists()) {
            return null;
        }

        $formattedPhone = self::formatPhoneNumber($rawPhone);

        $log = \App\Models\WhatsappLog::create([
            'user_id'        => $userId,
            'recipient_name' => $recipientName,
            'phone'          => $formattedPhone,
            'context'        => $context,
            'reference_key'  => $referenceKey,
            'message'        => $message,
            'status'         => $formattedPhone ? 'pending' : 'failed',
            'error_message'  => $formattedPhone ? null : 'Nomor telepon pengguna kosong atau tidak valid.',
        ]);

        if ($formattedPhone) {
            \App\Jobs\SendBaileysWhatsAppJob::dispatch($log->id);
        }

        return $log;
    }

    /**
     * 1. Kirim Notifikasi WA ke Guru Piket yang Bertugas (15 menit sebelum sesi atau saat sesi aktif)
     */
    public function notifyGuruPiketBertugas(\App\Models\Guru $guru, \App\Models\JadwalPiket $jadwal, bool $isPengganti = false): void
    {
        if (! (bool) \App\Models\Setting::get('wa_notify_piket_bertugas', true)) {
            return;
        }

        $today = now('Asia/Jakarta')->toDateString();
        // Kunci unik agar 1 guru pada 1 sesi di tanggal yang sama hanya dikirimi 1x
        $refKey = "piket_aktif_{$jadwal->id}_guru_{$guru->id}_{$today}";

        $templates = $this->getTemplates();
        $content = $templates['pengingat-piket'] ?? $templates['pengingat_piket'] ?? null;

        if (! $content) {
            $statusPeran = $isPengganti ? 'Guru Piket Pengganti' : 'Guru Piket Resmi';
            $content = "📋 *PENGINGAT JADWAL PIKET (DIDISPEN)*\n\nHalo Yth. Bapak/Ibu *{nama_guru}*,\n\nMengingatkan jadwal piket Anda hari ini sebagai *{$statusPeran}*:\n📅 Hari/Tanggal: *{hari}, {tanggal}*\n⏰ Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n👤 Koordinator: {koordinator}\n\nPengingat ini dikirimkan {menit_pengingat} sebelum sesi dimulai. Mohon untuk bersiap dan hadir di pos piket untuk memantau kehadiran serta dispensasi siswa.\n\nSelamat bertugas!\n- Sistem DIDISPEN SMKN 1 Bangsri";
        }

        $pesan = $this->renderPiketContent($content, $guru, $jadwal);

        $this->dispatchBaileysMessage(
            rawPhone: $guru->no_telepon,
            message: $pesan,
            context: 'piket_aktif',
            userId: $guru->user_id,
            recipientName: $guru->nama_lengkap,
            referenceKey: $refKey
        );
    }

    /**
     * Kirim Notifikasi WA ke Guru Pengganti saat ada permohonan pergantian piket baru
     */
    public function notifyGuruPenggantiSwap(\App\Models\PertukaranJadwalPiket $swap): void
    {
        if (! (bool) \App\Models\Setting::get('wa_notify_piket_swap', true)) {
            return;
        }

        $swap->loadMissing(['guruPengganti', 'guruAsal', 'jadwalPiket']);
        $guruPengganti = $swap->guruPengganti;
        $guruAsal = $swap->guruAsal;
        $jadwal = $swap->jadwalPiket;

        if (! $guruPengganti || ! $guruPengganti->status_aktif) {
            return;
        }

        $templates = $this->getTemplates();
        $content = $templates['permintaan-tukar-piket'] ?? null;

        if (! $content) {
            $content = "📋 *PERMINTAAN PENGGANTIAN JADWAL PIKET (DIDISPEN)*\n\nHalo Yth. Bapak/Ibu *{nama_guru_pengganti}*,\n\nBapak/Ibu *{nama_guru_asal}* mengajukan permohonan agar Anda berkenan menjadi Guru Pengganti untuk jadwal piket beliau pada:\n\n📅 Hari/Tanggal: *{hari}, {tanggal}*\n⏰ Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n📝 Alasan: _{alasan}_\n\nSilakan buka tautan berikut untuk meninjau dan merespon permohonan ini:\n{link_persetujuan}\n\nTerima kasih atas kerjasamanya.\n- Sistem DIDISPEN SMKN 1 Bangsri";
        }

        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $tanggalObj = $swap->tanggal instanceof \Carbon\CarbonInterface
            ? $swap->tanggal
            : \Carbon\Carbon::parse($swap->tanggal, 'Asia/Jakarta');
        $hariStr = $namaHari[$tanggalObj->dayOfWeekIso] ?? 'Hari ' . $tanggalObj->dayOfWeekIso;

        $linkPersetujuan = '-';
        try {
            $linkPersetujuan = route('guru.piket.swap.incoming');
        } catch (\Throwable) {
            $linkPersetujuan = url('/guru/piket/swap/incoming');
        }

        $replacements = [
            '{nama_guru_pengganti}' => $guruPengganti->nama_lengkap ?? 'Bapak/Ibu Guru',
            '{nama_guru_asal}'      => $guruAsal?->nama_lengkap ?? 'Guru Pemohon',
            '{hari}'                => $hariStr,
            '{tanggal}'             => $tanggalObj->translatedFormat('d F Y'),
            '{nama_sesi}'           => $jadwal?->nama_sesi ?? 'Sesi Piket',
            '{jam_mulai}'           => $jadwal?->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-',
            '{jam_selesai}'         => $jadwal?->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-',
            '{alasan}'              => $swap->alasan ?: '-',
            '{link_persetujuan}'    => $linkPersetujuan,
            '{link_web}'            => rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/'),
        ];

        $pesan = str_replace(array_keys($replacements), array_values($replacements), $content);

        $this->dispatchBaileysMessage(
            rawPhone: $guruPengganti->no_telepon,
            message: $pesan,
            context: 'piket_swap_request',
            userId: $guruPengganti->user_id,
            recipientName: $guruPengganti->nama_lengkap,
            referenceKey: "swap_req_{$swap->id}"
        );
    }

    /**
     * Kirim Notifikasi WA ke Guru Asal terkait hasil respon permohonan pertukaran piket (disetujui/ditolak)
     */
    public function notifyGuruAsalSwapResult(\App\Models\PertukaranJadwalPiket $swap, string $statusText = 'disetujui'): void
    {
        if (! (bool) \App\Models\Setting::get('wa_notify_piket_swap_result', true)) {
            return;
        }

        $swap->loadMissing(['guruPengganti', 'guruAsal', 'jadwalPiket']);
        $guruAsal = $swap->guruAsal;
        $guruPengganti = $swap->guruPengganti;
        $jadwal = $swap->jadwalPiket;

        if (! $guruAsal || ! $guruAsal->status_aktif) {
            return;
        }

        $templates = $this->getTemplates();
        $content = $templates['hasil-tukar-piket'] ?? null;

        if (! $content) {
            $content = "📢 *STATUS PERMINTAAN PENGGANTIAN PIKET (DIDISPEN)*\n\nHalo Yth. Bapak/Ibu *{nama_guru_asal}*,\n\nPermintaan penggantian jadwal piket Anda untuk tanggal *{tanggal}* ({nama_sesi}) telah *{status_persetujuan}* oleh Bapak/Ibu *{nama_guru_pengganti}*.\n\n{catatan_tambahan}\n\nWebsite DIDISPEN:\n{link_web}\n\nTerima kasih.\n- Sistem DIDISPEN SMKN 1 Bangsri";
        }

        $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $tanggalObj = $swap->tanggal instanceof \Carbon\CarbonInterface
            ? $swap->tanggal
            : \Carbon\Carbon::parse($swap->tanggal, 'Asia/Jakarta');
        $hariStr = $namaHari[$tanggalObj->dayOfWeekIso] ?? 'Hari ' . $tanggalObj->dayOfWeekIso;

        $catatanTambahan = $swap->catatan ? "Catatan dari Guru Pengganti:\n_{$swap->catatan}_\n" : "";

        $replacements = [
            '{nama_guru_asal}'      => $guruAsal->nama_lengkap ?? 'Bapak/Ibu Guru',
            '{nama_guru_pengganti}' => $guruPengganti?->nama_lengkap ?? 'Guru Pengganti',
            '{status_persetujuan}'  => strtoupper($statusText),
            '{catatan_tambahan}'    => $catatanTambahan,
            '{hari}'                => $hariStr,
            '{tanggal}'             => $tanggalObj->translatedFormat('d F Y'),
            '{nama_sesi}'           => $jadwal?->nama_sesi ?? 'Sesi Piket',
            '{jam_mulai}'           => $jadwal?->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-',
            '{jam_selesai}'         => $jadwal?->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-',
            '{link_web}'            => rtrim(config('app.url', 'https://didispen.smkn1bangsri.sch.id'), '/'),
        ];

        $pesan = str_replace(array_keys($replacements), array_values($replacements), $content);

        $this->dispatchBaileysMessage(
            rawPhone: $guruAsal->no_telepon,
            message: $pesan,
            context: 'piket_swap_result',
            userId: $guruAsal->user_id,
            recipientName: $guruAsal->nama_lengkap,
            referenceKey: "swap_res_{$swap->id}_{$swap->status}"
        );
    }

    /**
     * 2. Kirim Notifikasi WA ke Koordinator Guru Piket Hari Ini
     */
    public function notifyKoordinatorPiket(\App\Models\Guru $koordinator, \App\Models\JadwalPiket $jadwal, array $petugasList = []): void
    {
        if (! (bool) \App\Models\Setting::get('wa_notify_koordinator_piket', true)) {
            return;
        }

        $today = now('Asia/Jakarta')->toDateString();
        // Kunci unik harian agar Koordinator hanya dikirimi 1x sehari di sesi pertama
        $refKey = "piket_koor_daily_guru_{$koordinator->id}_{$today}";

        $templates = $this->getTemplates();
        $content = $templates['pengingat-koordinator']
            ?? $templates['pengingat_koordinator']
            ?? null;

        if (! $content) {
            $content = "Halo Yth. Bapak/Ibu *{nama_koordinator}*,\n\nIzin mengingatkan bahwa Anda bertugas sebagai *Koordinator Guru Piket* pada hari ini:\nHari: *{hari}*, {tanggal}\nSesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n\nDaftar Petugas Piket:\n{daftar_petugas}\n\nMohon bantuan Bapak/Ibu untuk mengecek dan memastikan petugas piket telah berada di tempatnya agar pelayanan dispensasi siswa dapat berjalan tertib.\n\nTerima kasih atas kerjasamanya.\n- Admin DIDISPEN SMK N 1 Bangsri";
        }

        $pesan = $this->renderKoordinatorPiketContent($content, $koordinator, $jadwal, $petugasList);

        $this->dispatchBaileysMessage(
            rawPhone: $koordinator->no_telepon,
            message: $pesan,
            context: 'piket_koordinator',
            userId: $koordinator->user_id,
            recipientName: $koordinator->nama_lengkap,
            referenceKey: $refKey
        );
    }

    /**
     * Kirim Notifikasi WA Pengingat Siaga di Pos Gerbang ke Satpam sesuai Sesi Guru Piket
     */
    public function notifySatpamStandbySesiPiket(\App\Models\JadwalPiket $jadwal, array $petugasList = []): void
    {
        if (! (bool) \App\Models\Setting::get('wa_notify_satpam_sesi_piket', true)) {
            return;
        }

        // Ambil nomor WA Satpam dari Pengaturan, atau fallback ke akun Satpam aktif
        $rawSatpamPhone = \App\Models\Setting::get('wa_satpam_phone');
        $satpamUserId = null;

        if (empty($rawSatpamPhone)) {
            // Fallback: cari user role satpam yang punya no_wa / no_telepon
            $satpamUser = \App\Models\User::where('role', 'satpam')
                ->where(function ($q) {
                    $q->whereNotNull('no_wa')->orWhereNotNull('no_telepon');
                })
                ->first();

            if ($satpamUser) {
                $rawSatpamPhone = $satpamUser->no_wa ?? $satpamUser->no_telepon;
                $satpamUserId = $satpamUser->id;
            }
        }

        if (empty($rawSatpamPhone)) {
            return;
        }

        $today = now('Asia/Jakarta')->toDateString();
        // Kunci unik harian agar Satpam hanya dikirimi 1x per sesi per hari
        $refKey = "satpam_sesi_{$jadwal->id}_{$today}";

        $templates = $this->getTemplates();
        $content = $templates['pengingat-satpam']
            ?? $templates['pengingat_satpam']
            ?? $templates['pengingat-satpam-standby-gerbang']
            ?? $templates['satpam-standby']
            ?? null;

        if (! $content) {
            $content = "🛡️ *PENGINGAT SIAGA POS GERBANG (DIDISPEN)*\n\nHalo Petugas Satpam, mohon perhatian & kesiapsiagaan di *Pos Gerbang Utama*:\n\n⏰ *Sesi Guru Piket Telah Dimulai:*\n- Sesi: *{nama_sesi}* ({jam_mulai} - {jam_selesai} WIB)\n- Guru Piket Bertugas:\n{daftar_petugas}\n- Koordinator Piket: {koordinator}\n\n📋 *Instruksi Operasional Pos Satpam:*\n1. Standby di Pos Gerbang untuk memantau siswa keluar dan masuk area sekolah.\n2. Pastikan setiap siswa membawa Surat Dispensasi Digital resmi dan lakukan *Scan QR Code Keluar / Masuk*.\n3. Jika ada siswa tanpa izin atau bermasalah, segera koordinasikan dengan Guru Piket yang bertugas di atas.\n\n🔗 *Buka Kamera Scanner Gerbang:*\n{link_scan_satpam}\n\nSelamat bertugas!\n- Sistem DIDISPEN SMKN 1 Bangsri";
        }

        $pesan = $this->renderSatpamContent($content, $jadwal, $petugasList);

        $this->dispatchBaileysMessage(
            rawPhone: $rawSatpamPhone,
            message: $pesan,
            context: 'satpam_sesi_piket',
            userId: $satpamUserId,
            recipientName: 'Pos Satpam Gerbang',
            referenceKey: $refKey
        );
    }

    /**
     * 3. Kirim Notifikasi WA ke Siswa yang Terlambat + Laporan ke Guru Piket yang Sedang Bertugas
     *
     * @param string $eventType 'overdue' (belum kembali & lewat batas) | 'returned_late' (sudah kembali tapi terlambat)
     */
    public function notifySiswaTerlambatDanGuruPiket(Dispensasi $dispensasi, string $eventType = 'overdue'): void
    {
        $dispensasi->loadMissing(['siswa.kelas.jurusan', 'siswa.user', 'guru']);
        $siswa = $dispensasi->siswa;
        if (! $siswa) {
            return;
        }

        // A. KIRIM WA KE SISWA (jika diaktifkan di Pengaturan)
        if ((bool) \App\Models\Setting::get('wa_notify_siswa_terlambat', true)) {
            $templates = $this->getTemplates();
            $contentSiswa = $templates['terlambat'] ?? $this->getDefaultContent('terlambat');
            $pesanSiswa = $this->renderContent($contentSiswa, $dispensasi, 'terlambat');

            $this->dispatchBaileysMessage(
                rawPhone: $siswa->no_telepon,
                message: $pesanSiswa,
                context: "siswa_terlambat_{$eventType}",
                userId: $siswa->user_id,
                recipientName: $siswa->nama_lengkap,
                referenceKey: "disp_{$dispensasi->id}_siswa_{$eventType}"
            );
        }

        // B. KIRIM LAPORAN KE GURU PIKET AKTIF (jika diaktifkan di Pengaturan)
        if ((bool) \App\Models\Setting::get('wa_notify_laporan_guru_piket', true)) {
            $piketService = app(GuruPiketService::class);
            $infoSesi = $piketService->getInformasiSesi();

            $daftarGuruPiket = collect();

            if ($infoSesi['status_sesi'] === 'Berlangsung' && ! $infoSesi['conflict']) {
                foreach ($infoSesi['petugas'] as $p) {
                    // Kirim ke guru piket yang statusnya 'Sedang Bertugas' atau 'Sudah Kembali'
                    if (in_array($p['status'], ['Sedang Bertugas', 'Sudah Kembali'])) {
                        $daftarGuruPiket->push($p['guru']);
                    }
                }
            }

            // Jika tidak ada sesi berlangsung / semua sedang keluar, fallback ke guru yang menyetujui dispensasi ini
            if ($daftarGuruPiket->isEmpty() && $dispensasi->guru && $dispensasi->guru->status_aktif) {
                $daftarGuruPiket->push($dispensasi->guru);
            }

            $durasiTerlambat = $dispensasi->getLateDurationText(false) ?: 'Baru saja melewati batas waktu';
            $kelasNama = $siswa->kelas?->nama_kelas ?? '-';
            $statusKet = $eventType === 'returned_late'
                ? "Sudah kembali di pos satpam pada pukul " . ($dispensasi->waktu_kembali_aktual?->format('H:i') ?? now()->format('H:i')) . " WIB (Terlambat {$durasiTerlambat})"
                : "Masih berada di luar sekolah & melewati batas waktu kembali ({$durasiTerlambat})";

            foreach ($daftarGuruPiket->unique('id') as $guruPiket) {
                $pesanGuru = "🚨 *LAPORAN SISWA TERLAMBAT (DIDISPEN)*\n\n"
                    . "Yth. Petugas Piket (*{$guruPiket->nama_lengkap}*),\n"
                    . "Terdapat siswa yang tercatat *TERLAMBAT KEMBALI* dari izin dispensasi:\n\n"
                    . "👤 Nama: *{$siswa->nama_lengkap}*\n"
                    . "🏫 Kelas: {$kelasNama}\n"
                    . "📄 No. Surat: {$dispensasi->nomor_surat}\n"
                    . "📍 Tujuan: " . ($dispensasi->tujuan ?? $dispensasi->lokasi ?? '-') . "\n"
                    . "⏰ Batas Kembali: {$dispensasi->jam_kembali} WIB\n"
                    . "⚠️ Status: *{$statusKet}*\n\n"
                    . "Mohon pantauan dan tindak lanjut dari Bapak/Ibu Guru Piket.\n"
                    . "- Sistem DIDISPEN";

                $this->dispatchBaileysMessage(
                    rawPhone: $guruPiket->no_telepon,
                    message: $pesanGuru,
                    context: "laporan_piket_{$eventType}",
                    userId: $guruPiket->user_id,
                    recipientName: $guruPiket->nama_lengkap,
                    referenceKey: "disp_{$dispensasi->id}_guru_{$guruPiket->id}_{$eventType}"
                );
            }
        }
    }

}


