<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class DispensasiTimeHelper
{
    public static function isWithinDispensasiTime(?Carbon $time = null): array
    {
        $now = $time ?? Carbon::now('Asia/Jakarta');
        $dayOfWeek = $now->dayOfWeek; // Integer (0-6)
        $currentTime = $now->format('H:i');

        $jamBuka = Setting::get('dispensasi_start_time', '07:00');
        $jamTutupRegular = Setting::get('dispensasi_end_time', '15:00');
        $jamTutupJumat = Setting::get('dispensasi_end_time_friday', '14:00');
        $allowedDays = Setting::get('dispensasi_days', '1,2,3,4,5');

        // ✅ PERBAIKAN: Ubah ke array integer untuk strict comparison
        $allowedDaysArray = array_map('intval', explode(',', $allowedDays));
        $jamTutup = ($dayOfWeek === 5) ? $jamTutupJumat : $jamTutupRegular;

        // 1. Cek Hari (Strict)
        if (!in_array($dayOfWeek, $allowedDaysArray, true)) {
            return [
                'allowed' => false,
                'reason' => 'Pengajuan dispensasi hanya dapat dilakukan pada hari yang diizinkan oleh admin.',
                'current_day' => $now->isoFormat('dddd'),
            ];
        }

        // 2. Cek Jam
        if ($currentTime < $jamBuka || $currentTime > $jamTutup) {
            return [
                'allowed' => false,
                'reason' => "Pengajuan dispensasi hanya dapat dilakukan pada pukul {$jamBuka} - {$jamTutup} WIB.",
                'current_time' => $currentTime,
                'allowed_time' => "{$jamBuka} - {$jamTutup} WIB",
            ];
        }

        return [
            'allowed' => true,
            'current_time' => $currentTime,
            'current_day' => $now->isoFormat('dddd'),
        ];
    }

    public static function getRestrictionMessage(): string
    {
        $jamBuka = Setting::get('dispensasi_start_time', '07:00');
        $jamTutupRegular = Setting::get('dispensasi_end_time', '15:00');
        $jamTutupJumat = Setting::get('dispensasi_end_time_friday', '14:00');
        $allowedDays = Setting::get('dispensasi_days', '1,2,3,4,5');

        $hariMap = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 0=>'Minggu'];
        $hariText = implode(', ', array_map(fn($d) => $hariMap[$d] ?? '', explode(',', $allowedDays)));

        return "Pengajuan dispensasi hanya dapat dilakukan pada hari <strong>{$hariText}</strong>, pukul <strong>{$jamBuka} - {$jamTutupRegular} WIB</strong> (Jumat s.d. {$jamTutupJumat} WIB).";
    }

    /**
     * ✅ METHOD INI YANG SEBELUMNYA HILANG/TIDAK TERBACA
     * Mendapatkan jumlah jam pelajaran maksimal berdasarkan hari
     */
    public static function getMaxJamPelajaran(?int $dayOfWeek = null): int
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now()->dayOfWeek;
        }

        // Jumat (5) = 8 jam pelajaran, hari lain (1-4) = 10 jam pelajaran
        return ($dayOfWeek === 5) ? 8 : 10;
    }

    /**
     * Hitung selisih menit keterlambatan
     */
    public static function hitungMenitTerlambat($batasWaktu, $referenceTime = null): int
    {
        if (empty($batasWaktu)) return 0;

        $batas = $batasWaktu instanceof \Carbon\Carbon ? $batasWaktu : \Carbon\Carbon::parse($batasWaktu);
        $referensi = $referenceTime ?? now();

        if ($referensi->lessThanOrEqualTo($batas)) return 0;

        return (int) ceil($batas->diffInSeconds($referensi) / 60);
    }

    /**
     * Format teks keterlambatan
     */
    public static function formatDurasiTerlambat(int $menit, bool $short = false): string
    {
        if ($menit <= 0) return '0 menit';

        $jam = floor($menit / 60);
        $sisaMenit = $menit % 60;

        if ($short) {
            return $jam > 0 ? "{$jam}j {$sisaMenit}m" : "{$menit}m";
        }

        $bagian = [];
        if ($jam > 0) $bagian[] = "{$jam} jam";
        if ($sisaMenit > 0 || $jam === 0) $bagian[] = "{$sisaMenit} menit";

        return implode(' ', $bagian);
    }
}
