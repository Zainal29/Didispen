<?php

namespace App\Helpers;

use App\Models\Dispensasi;
use App\Models\Setting;

class PrintHelper
{
    public const PRINTABLE_STATUSES = ['disetujui', 'keluar', 'selesai'];

    public static function maxStudentLimit(): int
    {
        return (int) Setting::get('student_print_limit', 3);
    }

    public static function maxTeacherLimit(): int
    {
        return (int) Setting::get('teacher_print_limit', 10);
    }

    public static function startTime(): string
    {
        return (string) Setting::get('print_start_time', '06:00');
    }

    public static function endTime(): string
    {
        return (string) Setting::get('print_end_time', '17:00');
    }

    public static function currentTime(): string
    {
        return now()->format('H:i');
    }

    public static function isWithinOperatingHours(?string $currentTime = null): bool
    {
        $currentTime = $currentTime ?? self::currentTime();
        return $currentTime >= self::startTime() && $currentTime <= self::endTime();
    }

    public static function canStudentPrint(Dispensasi $dispensasi): bool
    {
        return in_array($dispensasi->status, self::PRINTABLE_STATUSES, true)
            && ($dispensasi->student_print_count ?? 0) < self::maxStudentLimit()
            && self::isWithinOperatingHours();
    }

    public static function getStudentBlockReason(Dispensasi $dispensasi): ?string
    {
        if (!in_array($dispensasi->status, self::PRINTABLE_STATUSES, true)) {
            return 'Surat hanya bisa dicetak setelah mendapatkan persetujuan dari guru piket.';
        }

        $maxLimit = self::maxStudentLimit();
        if (($dispensasi->student_print_count ?? 0) >= $maxLimit) {
            return "Batas maksimal cetak siswa telah tercapai ({$maxLimit} kali). Silakan minta bantuan Guru Piket untuk mencetak.";
        }

        if (!self::isWithinOperatingHours()) {
            return self::operatingHoursMessage();
        }

        return null;
    }

    public static function canTeacherPrint(Dispensasi $dispensasi): bool
    {
        return in_array($dispensasi->status, self::PRINTABLE_STATUSES, true)
            && ($dispensasi->teacher_print_count ?? 0) < self::maxTeacherLimit()
            && self::isWithinOperatingHours();
    }

    public static function getTeacherBlockReason(Dispensasi $dispensasi): ?string
    {
        if (!in_array($dispensasi->status, self::PRINTABLE_STATUSES, true)) {
            return 'Surat hanya bisa dicetak setelah mendapatkan persetujuan.';
        }

        $maxLimit = self::maxTeacherLimit();
        if (($dispensasi->teacher_print_count ?? 0) >= $maxLimit) {
            return "Batas maksimal cetak guru telah tercapai ({$maxLimit} kali).";
        }

        if (!self::isWithinOperatingHours()) {
            return self::operatingHoursMessage();
        }

        return null;
    }

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
