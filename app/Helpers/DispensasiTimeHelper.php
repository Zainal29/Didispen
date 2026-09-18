<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class DispensasiTimeHelper
{
    public static function isWithinDispensasiTime(?Carbon $time = null): array
    {
        $now = $time ?? Carbon::now('Asia/Jakarta');
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i');

        // ✅ FORCE REFRESH: Clear cache untuk setting ini
        $jamBuka = Setting::get('dispensasi_start_time', '07:00');
        $jamTutupRegular = Setting::get('dispensasi_end_time', '15:00');
        $jamTutupJumat = Setting::get('dispensasi_end_time_friday', '14:00');
        $allowedDays = Setting::get('dispensasi_days', '1,2,3,4,5');

        $allowedDaysArray = array_map('intval', explode(',', $allowedDays));
        $jamTutup = ($dayOfWeek === 5) ? $jamTutupJumat : $jamTutupRegular;

        // ✅ DEBUG LOG
        \Illuminate\Support\Facades\Log::info('Time Validation', [
            'current_time' => $currentTime,
            'jam_buka' => $jamBuka,
            'jam_tutup' => $jamTutup,
            'day_of_week' => $dayOfWeek,
            'allowed_days' => $allowedDaysArray,
        ]);

        // 1. Cek Hari
        if (!in_array($dayOfWeek, $allowedDaysArray, true)) {
            return [
                'allowed' => false,
                'reason' => 'Pengajuan dispensasi hanya dapat dilakukan pada hari yang diizinkan oleh admin.',
                'current_day' => $now->isoFormat('dddd'),
            ];
        }

        // 2. Cek Jam (String comparison untuk format HH:MM)
        if ($currentTime < $jamBuka || $currentTime > $jamTutup) {
            return [
                'allowed' => false,
                'reason' => "Pengajuan dispensasi hanya dapat dilakukan pada pukul {$jamBuka} - {$jamTutup} WIB. Waktu saat ini: {$currentTime} WIB.",
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
    /**
     * ✅ METHOD INI DINAMIS: Mendapatkan jumlah jam pelajaran maksimal berdasarkan hari
     * Dihitung dari jumlah entri yang diisi di pengaturan Admin
     */
    public static function getMaxJamPelajaran(?int $dayOfWeek = null): int
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now()->dayOfWeek;
        }

        $defaultJadwal = json_encode([
            'regular' => array_fill(1, 10, ['start' => '00:00', 'end' => '00:00']),
            'friday'  => array_fill(1, 8, ['start' => '00:00', 'end' => '00:00'])
        ]);

        $jadwalData = json_decode(Setting::get('jam_pelajaran', $defaultJadwal), true);

        if ($dayOfWeek === 5) {
            // Hitung jumlah jam yang diisi untuk Jumat, fallback ke 8
            $count = count(array_filter($jadwalData['friday'] ?? [], fn($j) => !empty($j['start'])));
            return $count > 0 ? $count : 8;
        }

        // Hitung jumlah jam yang diisi untuk Regular, fallback ke 10
        $count = count(array_filter($jadwalData['regular'] ?? [], fn($j) => !empty($j['start'])));
        return $count > 0 ? $count : 10;
    }
    /**
     * Hitung selisih menit keterlambatan
     */
    // public static function hitungMenitTerlambat($batasWaktu, $referenceTime = null): int
    // {
    //     if (empty($batasWaktu)) return 0;

    //     $batas = $batasWaktu instanceof \Carbon\Carbon ? $batasWaktu : \Carbon\Carbon::parse($batasWaktu);
    //     $referensi = $referenceTime ?? now();

    //     if ($referensi->lessThanOrEqualTo($batas)) return 0;

    //     return (int) ceil($batas->diffInSeconds($referensi) / 60);
    // }
    public static function hitungMenitTerlambat(
        $batasWaktu,
        $referenceTime = null
    ): int {
        if (!$batasWaktu) {
            return 0;
        }

        $timezone = config('app.timezone', 'Asia/Jakarta');

        $batas = $batasWaktu instanceof Carbon
            ? $batasWaktu->copy()->setTimezone($timezone)
            : Carbon::parse($batasWaktu, $timezone);

        $referensi = $referenceTime
            ? (
                $referenceTime instanceof Carbon
                    ? $referenceTime->copy()->setTimezone($timezone)
                    : Carbon::parse($referenceTime, $timezone)
            )
            : now($timezone);

        if ($referensi->lessThanOrEqualTo($batas)) {
            return 0;
        }

        return (int) floor(
            $batas->diffInSeconds($referensi) / 60
        );
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
