<?php

namespace App\Helpers;

use App\Models\Dispensasi;
use App\Models\Setting;

/**
 * PrintHelper
 *
 * Satu sumber kebenaran (single source of truth) untuk seluruh logika
 * validasi pencetakan struk/surat dispensasi. Dipakai oleh:
 *  - PrintLimitMiddleware
 *  - Siswa\CetakController
 *  - Guru\CetakStrukController (via middleware)
 *  - View dashboard & detail (siswa & guru)
 *
 * Pengaturan dibaca dari tabel `settings` (bisa diubah Admin):
 *  - print_max_limit   : batas maksimal cetak per dispensasi (default 3)
 *  - print_start_time  : jam mulai operasional cetak (default 06:00)
 *  - print_end_time    : jam akhir operasional cetak (default 17:00)
 */
class PrintHelper
{
    /**
     * Status dispensasi yang boleh dicetak.
     */
    public const PRINTABLE_STATUSES = ['disetujui', 'keluar', 'selesai'];

    /**
     * Batas maksimal cetak (global, dari pengaturan Admin).
     */
    public static function maxLimit(): int
    {
        return (int) Setting::get('print_max_limit', 3);
    }

    /**
     * Jam mulai operasional cetak, format H:i.
     */
    public static function startTime(): string
    {
        return (string) Setting::get('print_start_time', '06:00');
    }

    /**
     * Jam akhir operasional cetak, format H:i.
     */
    public static function endTime(): string
    {
        return (string) Setting::get('print_end_time', '17:00');
    }

    /**
     * Waktu sekarang dalam format H:i (WIB / Asia-Jakarta).
     */
    public static function currentTime(): string
    {
        return now()->format('H:i');
    }

    /**
     * Apakah waktu sekarang berada dalam jam operasional cetak?
     */
    public static function isWithinOperatingHours(?string $currentTime = null): bool
    {
        $currentTime = $currentTime ?? self::currentTime();

        return $currentTime >= self::startTime() && $currentTime <= self::endTime();
    }

    /**
     * Apakah dispensasi boleh dicetak saat ini?
     * (status valid + belum mencapai batas + dalam jam operasional)
     */
    public static function canPrint(Dispensasi $dispensasi): bool
    {
        return in_array($dispensasi->status, self::PRINTABLE_STATUSES, true)
            && $dispensasi->print_count < self::maxLimit()
            && self::isWithinOperatingHours();
    }

    /**
     * Alasan mengapa dispensasi TIDAK bisa dicetak (null jika bisa dicetak).
     * Dipakai untuk pesan redirect maupun teks tombol/tooltip.
     */
    public static function blockReason(Dispensasi $dispensasi): ?string
    {
        if (! in_array($dispensasi->status, self::PRINTABLE_STATUSES, true)) {
            return 'Surat/struk hanya bisa dicetak setelah mendapatkan persetujuan dari guru piket.';
        }

        $maxLimit = self::maxLimit();
        if ($dispensasi->print_count >= $maxLimit) {
            return "Batas maksimal cetak surat telah tercapai ({$maxLimit} kali). "
                .'Silakan hubungi admin jika membutuhkan cetak ulang.';
        }

        if (! self::isWithinOperatingHours()) {
            return self::operatingHoursMessage();
        }

        return null;
    }

    /**
     * Pesan error standar untuk pelanggaran jam operasional cetak.
     * Format: "Pencetakan struk hanya diperbolehkan pada pukul 06:00 - 17:00 WIB.
     *          Saat ini pukul 20:15 WIB."
     */
    public static function operatingHoursMessage(?string $currentTime = null): string
    {
        $currentTime = $currentTime ?? self::currentTime();

        return sprintf(
            'Pencetakan struk hanya diperbolehkan pada pukul %s - %s WIB. Saat ini pukul %s WIB.',
            self::startTime(),
            self::endTime(),
            $currentTime
        );
    }
}
