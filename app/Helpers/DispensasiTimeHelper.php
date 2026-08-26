<?php

namespace App\Helpers;

use Carbon\Carbon;

class DispensasiTimeHelper
{
    /**
     * Cek apakah sekarang dalam jam pengajuan dispensasi
     *
     * Aturan:
     * - Senin-Kamis: 08.00 - 15.00 WIB
     * - Jumat: 08.00 - 14.00 WIB (sesuai jadwal sesi 3)
     * - Sabtu-Minggu: Tidak bisa
     */
    public static function isWithinDispensasiTime(?Carbon $time = null): array
    {
        $now = $time ?? Carbon::now('Asia/Jakarta');

        $dayOfWeek = $now->dayOfWeek; // 0=Minggu, 1=Senin, ..., 6=Sabtu
        $currentTime = $now->format('H:i');

        // Cek hari (Senin=1 sampai Jumat=5)
        if ($dayOfWeek < 1 || $dayOfWeek > 5) {
            return [
                'allowed' => false,
                'reason' => 'Pengajuan dispensasi hanya dapat dilakukan pada hari Senin sampai Jumat.',
                'current_day' => $now->isoFormat('dddd'),
                'allowed_days' => 'Senin - Jumat',
            ];
        }

        // Tentukan jam tutup berdasarkan hari
        $jamBuka = '08:00';
        $jamTutup = ($dayOfWeek === 5) ? '14:00' : '15:00'; // Jumat tutup lebih awal

        if ($currentTime < $jamBuka || $currentTime > $jamTutup) {
            return [
                'allowed' => false,
                'reason' => "Pengajuan dispensasi hanya dapat dilakukan pada pukul {$jamBuka} - {$jamTutup} WIB.",
                'current_time' => $currentTime,
                'allowed_time' => "{$jamBuka} - {$jamTutup} WIB",
                'current_day' => $now->isoFormat('dddd'),
            ];
        }

        return [
            'allowed' => true,
            'current_time' => $currentTime,
            'current_day' => $now->isoFormat('dddd'),
        ];
    }

    /**
     * Dapatkan pesan lengkap untuk ditampilkan ke user
     */
    public static function getRestrictionMessage(): string
    {
        $now = Carbon::now('Asia/Jakarta');
        $dayOfWeek = $now->dayOfWeek;

        if ($dayOfWeek < 1 || $dayOfWeek > 5) {
            return 'Pengajuan dispensasi hanya dapat dilakukan pada hari <strong>Senin sampai Jumat</strong>.';
        }

        $jamTutup = ($dayOfWeek === 5) ? '14:00' : '15:00';

        return "Pengajuan dispensasi hanya dapat dilakukan pada pukul <strong>08:00 - {$jamTutup} WIB</strong>.";
    }
}
