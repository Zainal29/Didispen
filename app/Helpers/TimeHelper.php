<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class TimeHelper
{
    /**
     * Jadwal default acuan (Single Source of Truth jika belum diset di Setting)
     */
    public static function getDefaultJadwal(): array
    {
        return [
            'senin_selasa' => [
                1  => ['start' => '07:00', 'end' => '07:45'],
                2  => ['start' => '07:45', 'end' => '08:30'],
                3  => ['start' => '08:30', 'end' => '09:15'],
                4  => ['start' => '09:30', 'end' => '10:15'],
                5  => ['start' => '10:15', 'end' => '11:00'],
                6  => ['start' => '11:00', 'end' => '11:45'],
                7  => ['start' => '12:15', 'end' => '13:00'],
                8  => ['start' => '13:00', 'end' => '13:45'],
                9  => ['start' => '13:45', 'end' => '14:30'],
                10 => ['start' => '14:30', 'end' => '15:15'],
            ],
            'rabu_kamis' => [
                1  => ['start' => '07:00', 'end' => '07:40'],
                2  => ['start' => '07:40', 'end' => '08:20'],
                3  => ['start' => '08:20', 'end' => '09:00'],
                4  => ['start' => '09:15', 'end' => '09:55'],
                5  => ['start' => '09:55', 'end' => '10:35'],
                6  => ['start' => '10:35', 'end' => '11:15'],
                7  => ['start' => '11:15', 'end' => '11:55'],
                8  => ['start' => '12:30', 'end' => '13:10'],
                9  => ['start' => '13:10', 'end' => '13:50'],
                10 => ['start' => '13:50', 'end' => '14:30'],
                11 => ['start' => '14:30', 'end' => '15:10'],
            ],
            'jumat' => [
                1 => ['start' => '08:00', 'end' => '08:35'],
                2 => ['start' => '08:35', 'end' => '09:10'],
                3 => ['start' => '09:30', 'end' => '10:00'],
                4 => ['start' => '10:00', 'end' => '10:30'],
                5 => ['start' => '10:30', 'end' => '11:00'],
                6 => ['start' => '11:00', 'end' => '11:30'],
                7 => ['start' => '12:40', 'end' => '13:20'],
                8 => ['start' => '13:20', 'end' => '14:00'],
            ],
            'sabtu' => [
                1  => ['start' => '07:00', 'end' => '07:45'],
                2  => ['start' => '07:45', 'end' => '08:30'],
                3  => ['start' => '08:30', 'end' => '09:15'],
                4  => ['start' => '09:30', 'end' => '10:15'],
                5  => ['start' => '10:15', 'end' => '11:00'],
                6  => ['start' => '11:00', 'end' => '11:45'],
                7  => ['start' => '12:15', 'end' => '13:00'],
                8  => ['start' => '13:00', 'end' => '13:45'],
                9  => ['start' => '13:45', 'end' => '14:30'],
                10 => ['start' => '14:30', 'end' => '15:15'],
            ],
            'minggu' => [
                1  => ['start' => '07:00', 'end' => '07:45'],
                2  => ['start' => '07:45', 'end' => '08:30'],
                3  => ['start' => '08:30', 'end' => '09:15'],
                4  => ['start' => '09:30', 'end' => '10:15'],
                5  => ['start' => '10:15', 'end' => '11:00'],
                6  => ['start' => '11:00', 'end' => '11:45'],
                7  => ['start' => '12:15', 'end' => '13:00'],
                8  => ['start' => '13:00', 'end' => '13:45'],
                9  => ['start' => '13:45', 'end' => '14:30'],
                10 => ['start' => '14:30', 'end' => '15:15'],
            ],
        ];
    }

    /**
     * Normalisasi payload jadwal dari database atau format lama
     */
    public static function normalizeJadwal(mixed $data): array
    {
        $default = self::getDefaultJadwal();

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($data) || empty($data)) {
            return $default;
        }

        // Kompatibilitas data format lama ('regular', 'friday')
        $seninSelasa = $data['senin_selasa'] ?? $data['regular'] ?? $default['senin_selasa'];
        $rabuKamis   = $data['rabu_kamis'] ?? $default['rabu_kamis'];
        $jumat       = $data['jumat'] ?? $data['friday'] ?? $default['jumat'];
        $sabtu       = $data['sabtu'] ?? $default['sabtu'];
        $minggu      = $data['minggu'] ?? $default['minggu'];

        return [
            'senin_selasa' => self::sanitizeSlots($seninSelasa, 10, $default['senin_selasa']),
            'rabu_kamis'   => self::sanitizeSlots($rabuKamis, 11, $default['rabu_kamis']),
            'jumat'        => self::sanitizeSlots($jumat, 8, $default['jumat']),
            'sabtu'        => self::sanitizeSlots($sabtu, 10, $default['sabtu']),
            'minggu'       => self::sanitizeSlots($minggu, 10, $default['minggu']),
        ];
    }

    /**
     * Memastikan setiap slot jam memiliki format integer key dengan 'start' dan 'end'
     */
    private static function sanitizeSlots(array $slots, int $maxJam, array $fallbackSlots): array
    {
        $sanitized = [];
        for ($i = 1; $i <= $maxJam; $i++) {
            $slot = $slots[$i] ?? $slots[(string) $i] ?? $fallbackSlots[$i] ?? null;
            if ($slot && ! empty($slot['start']) && ! empty($slot['end'])) {
                $sanitized[$i] = [
                    'start' => substr((string) $slot['start'], 0, 5),
                    'end'   => substr((string) $slot['end'], 0, 5),
                ];
            } elseif (isset($fallbackSlots[$i])) {
                $sanitized[$i] = $fallbackSlots[$i];
            }
        }
        return $sanitized;
    }

    /**
     * Mengambil seluruh jadwal dari Setting dengan fallback aman
     */
    public static function getAllJadwal(): array
    {
        $raw = Setting::get('jam_pelajaran');
        return self::normalizeJadwal($raw);
    }

    /**
     * Mendapatkan key kelompok jadwal berdasarkan hari (0=Minggu, 1=Senin s.d. 6=Sabtu)
     */
    public static function getJadwalKeyByDay(?int $dayOfWeek = null): ?string
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        return match ($dayOfWeek) {
            0       => 'minggu',
            1, 2    => 'senin_selasa',
            3, 4    => 'rabu_kamis',
            5       => 'jumat',
            6       => 'sabtu',
            default => null,
        };
    }

    /**
     * Mengambil jadwal hari ini atau hari tertentu
     */
    public static function getJadwalHari(?int $dayOfWeek = null): array
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        $all = self::getAllJadwal();
        $key = self::getJadwalKeyByDay($dayOfWeek);

        if (! $key || ! isset($all[$key])) {
            return [];
        }

        return $all[$key];
    }

    /**
     * Mendapatkan jumlah jam pelajaran maksimal pada hari tertentu
     */
    public static function getMaxJamPelajaran(?int $dayOfWeek = null): int
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        $jadwalHari = self::getJadwalHari($dayOfWeek);
        if (empty($jadwalHari)) {
            return 0;
        }

        $count = count(array_filter($jadwalHari, fn($s) => ! empty($s['start']) && ! empty($s['end'])));

        if ($count > 0) {
            return $count;
        }

        // Fallback berdasarkan hari
        return match ($dayOfWeek) {
            0       => 10,
            1, 2    => 10,
            3, 4    => 11,
            5       => 8,
            6       => 10,
            default => 0,
        };
    }

    /**
     * Mengubah teks atau angka jam pelajaran menjadi rentang waktu aktual "HH:MM - HH:MM"
     * Digunakan oleh Siswa, Guru, Admin Show, Satpam Card, dan QRScanService.
     *
     * @param string|int $teksJam Contoh: "Jam Pelajaran ke-5", "Jam ke-5", atau 5
     * @param int|null $dayOfWeek Day of week (0=Minggu s.d. 6=Sabtu)
     * @return string Format: "HH:MM - HH:MM" atau "-" jika tidak ditemukan
     */
    public static function getWaktuAktual(string|int|null $teksJam, ?int $dayOfWeek = null): string
    {
        if ($teksJam === null || $teksJam === '') {
            return '-';
        }

        // Ekstrak angka dari string
        preg_match('/(\d+)/', (string) $teksJam, $matches);
        $angka = isset($matches[1]) ? (int) $matches[1] : 0;

        if ($angka <= 0) {
            return '-';
        }

        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        $jadwalHari = self::getJadwalHari($dayOfWeek);

        // Fallback untuk hari libur (Sabtu/Minggu) jika melihat riwayat dispensasi
        if (empty($jadwalHari)) {
            $all = self::getAllJadwal();
            $jadwalHari = $all['senin_selasa'] ?? [];
        }

        if (isset($jadwalHari[$angka]) && ! empty($jadwalHari[$angka]['start']) && ! empty($jadwalHari[$angka]['end'])) {
            return $jadwalHari[$angka]['start'] . ' - ' . $jadwalHari[$angka]['end'];
        }

        return '-';
    }

    /**
     * Menghitung batas waktu kembali (Carbon instance) berdasarkan jam kembali dan hari
     */
    public static function getBatasWaktuKembali(int|string|null $jamKembali, ?int $dayOfWeek = null): ?Carbon
    {
        if ($jamKembali === null || $jamKembali === '') {
            return null;
        }

        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        $waktuAktual = self::getWaktuAktual($jamKembali, $dayOfWeek);

        if ($waktuAktual !== '-' && str_contains($waktuAktual, '-')) {
            $parts = array_map('trim', explode('-', $waktuAktual, 2));
            if (isset($parts[1]) && preg_match('/^\d{2}:\d{2}$/', $parts[1])) {
                return Carbon::today('Asia/Jakarta')->setTimeFromTimeString($parts[1]);
            }
        }

        return null;
    }
}
