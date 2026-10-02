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
     * Jadwal istirahat & pembiasaan default acuan (Sesuai KBM SMKN 1 Bangsri per 18 Agustus 2026)
     */
    public static function getDefaultIstirahat(): array
    {
        return [
            'senin_selasa' => [
                1 => ['label' => 'Istirahat 1', 'start' => '09:15', 'end' => '09:30'],
                2 => ['label' => 'Istirahat 2', 'start' => '11:45', 'end' => '12:15'],
            ],
            'rabu_kamis' => [
                1 => ['label' => 'Istirahat 1', 'start' => '09:00', 'end' => '09:15'],
                2 => ['label' => 'Istirahat 2', 'start' => '11:55', 'end' => '12:30'],
            ],
            'jumat' => [
                0 => ['label' => 'Pembiasaan', 'start' => '07:00', 'end' => '08:00'],
                1 => ['label' => 'Istirahat 1', 'start' => '09:10', 'end' => '09:30'],
                2 => ['label' => 'Istirahat 2 (Jumat)', 'start' => '11:30', 'end' => '12:40'],
            ],
            'sabtu' => [
                1 => ['label' => 'Istirahat 1', 'start' => '09:15', 'end' => '09:30'],
                2 => ['label' => 'Istirahat 2', 'start' => '11:45', 'end' => '12:15'],
            ],
            'minggu' => [
                1 => ['label' => 'Istirahat 1', 'start' => '09:15', 'end' => '09:30'],
                2 => ['label' => 'Istirahat 2', 'start' => '11:45', 'end' => '12:15'],
            ],
        ];
    }

    /**
     * Mengambil seluruh jadwal istirahat dari Setting dengan fallback aman
     */
    public static function getAllIstirahat(): array
    {
        $raw = Setting::get('jam_istirahat');
        return self::normalizeIstirahat($raw);
    }

    /**
     * Normalisasi payload jadwal istirahat dari database
     */
    public static function normalizeIstirahat(mixed $data): array
    {
        $default = self::getDefaultIstirahat();

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($data) || empty($data)) {
            return $default;
        }

        $result = [];
        foreach ($default as $dayKey => $defaultSlots) {
            $result[$dayKey] = [];
            $slots = $data[$dayKey] ?? [];
            foreach ($defaultSlots as $idx => $def) {
                $slot = $slots[$idx] ?? $slots[(string) $idx] ?? $def;
                $start = ! empty($slot['start']) ? substr((string) $slot['start'], 0, 5) : $def['start'];
                $end   = ! empty($slot['end'])   ? substr((string) $slot['end'], 0, 5)   : $def['end'];
                $label = $slot['label'] ?? $def['label'];
                $result[$dayKey][$idx] = [
                    'label' => $label,
                    'start' => $start,
                    'end'   => $end,
                ];
            }
        }

        return $result;
    }

    /**
     * Mengambil jadwal istirahat hari tertentu
     */
    public static function getIstirahatHari(?int $dayOfWeek = null): array
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        $all = self::getAllIstirahat();
        $key = self::getJadwalKeyByDay($dayOfWeek);

        if (! $key || ! isset($all[$key])) {
            return [];
        }

        return $all[$key];
    }

    /**
     * Mengambil gabungan seluruh slot (KBM + Istirahat/Pembiasaan) terurut kronologis
     */
    public static function getSemuaSlotHari(?int $dayOfWeek = null): array
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
        }

        $jadwalHari = self::getJadwalHari($dayOfWeek);
        $istirahatHari = self::getIstirahatHari($dayOfWeek);

        $slots = [];

        foreach ($jadwalHari as $jamKe => $slot) {
            if (! empty($slot['start']) && ! empty($slot['end'])) {
                $slots[] = [
                    'type'   => 'kbm',
                    'jam_ke' => (int) $jamKe,
                    'label'  => 'Jam ' . $jamKe,
                    'start'  => substr((string) $slot['start'], 0, 5),
                    'end'    => substr((string) $slot['end'], 0, 5),
                ];
            }
        }

        foreach ($istirahatHari as $idx => $slot) {
            if (! empty($slot['start']) && ! empty($slot['end'])) {
                $label = $slot['label'] ?? ('Istirahat ' . $idx);
                $slots[] = [
                    'type'   => strtolower($label) === 'pembiasaan' ? 'pembiasaan' : 'istirahat',
                    'jam_ke' => null,
                    'label'  => $label,
                    'start'  => substr((string) $slot['start'], 0, 5),
                    'end'    => substr((string) $slot['end'], 0, 5),
                ];
            }
        }

        usort($slots, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        return $slots;
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
    // public static function getWaktuAktual(string|int|null $teksJam, ?int $dayOfWeek = null): string
    // {
    //     if ($teksJam === null || $teksJam === '') {
    //         return '-';
    //     }

    //     // Ekstrak angka dari string
    //     preg_match('/(\d+)/', (string) $teksJam, $matches);
    //     $angka = isset($matches[1]) ? (int) $matches[1] : 0;

    //     if ($angka <= 0) {
    //         return '-';
    //     }

    //     if ($dayOfWeek === null) {
    //         $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
    //     }

    //     $jadwalHari = self::getJadwalHari($dayOfWeek);

    //     // Fallback untuk hari libur (Sabtu/Minggu) jika melihat riwayat dispensasi
    //     if (empty($jadwalHari)) {
    //         $all = self::getAllJadwal();
    //         $jadwalHari = $all['senin_selasa'] ?? [];
    //     }

    //     if (isset($jadwalHari[$angka]) && ! empty($jadwalHari[$angka]['start']) && ! empty($jadwalHari[$angka]['end'])) {
    //         return $jadwalHari[$angka]['start'] . ' - ' . $jadwalHari[$angka]['end'];
    //     }

    //     return '-';
    // }

    /**
 * Mengubah nilai jam dispensasi menjadi waktu aktual.
 *
 * Kompatibel dengan:
 * - Data lama: "Jam Pelajaran ke-5"
 * - Data lama: "Jam ke-5"
 * - Data lama: 5
 * - Data baru: "09:30"
 *
 * Untuk data lama:
 *     "Jam Pelajaran ke-5" -> "10:15 - 11:00"
 *
 * Untuk data baru:
 *     "09:30" -> "09:30"
 */
public static function getWaktuAktual(
    string|int|null $teksJam,
    ?int $dayOfWeek = null
): string {
    if ($teksJam === null || $teksJam === '') {
        return '-';
    }

    $nilai = trim((string) $teksJam);

    /*
     * FORMAT BARU
     * Contoh:
     * 09:30
     * 10:15
     *
     * Jangan diproses sebagai nomor jam pelajaran.
     */
    if (preg_match('/^\d{2}:\d{2}$/', $nilai)) {
        return $nilai;
    }

    /*
     * FORMAT LAMA
     * Contoh:
     * "Jam Pelajaran ke-5"
     * "Jam ke-5"
     * "5"
     */
    preg_match('/(\d+)/', $nilai, $matches);

    $angka = isset($matches[1]) ? (int) $matches[1] : 0;

    if ($angka <= 0) {
        return '-';
    }

    if ($dayOfWeek === null) {
        $dayOfWeek = now('Asia/Jakarta')->dayOfWeek;
    }

    // Cek jika teks merujuk ke istirahat atau pembiasaan
    $istirahatHari = self::getIstirahatHari($dayOfWeek);
    foreach ($istirahatHari as $slot) {
        if (! empty($slot['label']) && stripos($nilai, $slot['label']) !== false && ! empty($slot['start']) && ! empty($slot['end'])) {
            return $slot['start'] . ' - ' . $slot['end'];
        }
    }

    $jadwalHari = self::getJadwalHari($dayOfWeek);

    /*
     * Fallback untuk hari libur ketika melihat
     * riwayat dispensasi lama.
     */
    if (empty($jadwalHari)) {
        $all = self::getAllJadwal();
        $jadwalHari = $all['senin_selasa'] ?? [];
    }

    if (
        isset($jadwalHari[$angka])
        && ! empty($jadwalHari[$angka]['start'])
        && ! empty($jadwalHari[$angka]['end'])
    ) {
        return $jadwalHari[$angka]['start']
            . ' - '
            . $jadwalHari[$angka]['end'];
    }

    return '-';
}

    public static function getBatasWaktuKembali(int|string|null $jamKembali, ?int $dayOfWeek = null): ?Carbon
    {
        if ($jamKembali === null || $jamKembali === '') {
            return null;
        }

        $nilai = trim((string) $jamKembali);

        // Format waktu langsung (HH:MM atau HH:MM:SS)
        if (preg_match('/^(\d{1,2}):(\d{2})(:00)?$/', $nilai, $m)) {
            return Carbon::today('Asia/Jakarta')->setTime((int)$m[1], (int)$m[2], 0);
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

    /**
     * Mendapatkan waktu selesai jam KBM terakhir pada tanggal/hari tertentu (Single Source of Truth)
     */
    public static function getWaktuSelesaiKbmTerakhir(?int $dayOfWeek = null, ?Carbon $date = null): ?Carbon
    {
        $targetDate = $date ? $date->copy()->setTimezone('Asia/Jakarta') : now('Asia/Jakarta');

        if ($dayOfWeek === null) {
            $dayOfWeek = $targetDate->dayOfWeek;
        }

        $jadwalHari = self::getJadwalHari($dayOfWeek);
        $maxJam = self::getMaxJamPelajaran($dayOfWeek);

        if ($maxJam > 0 && isset($jadwalHari[$maxJam]['end']) && ! empty($jadwalHari[$maxJam]['end'])) {
            return $targetDate->copy()->setTimeFromTimeString($jadwalHari[$maxJam]['end']);
        }

        // Fallback berdasarkan hari jika setting kosong
        $fallbackEnd = match ($dayOfWeek) {
            5       => '14:00',
            3, 4    => '15:10',
            default => '15:15',
        };

        return $targetDate->copy()->setTimeFromTimeString($fallbackEnd);
    }

    /**
     * Memeriksa apakah waktu sekarang sudah melewati jam KBM terakhir pada tanggal dispensasi
     *
     * @param Carbon|null $now Waktu sekarang (default: now('Asia/Jakarta'))
     * @param Carbon|null $dispensasiDate Tanggal pengajuan dispensasi dibuat (default: now('Asia/Jakarta'))
     * @return bool
     */
    public static function isKbmHariSelesai(?Carbon $now = null, ?Carbon $dispensasiDate = null): bool
    {
        $now = $now ? $now->copy()->setTimezone('Asia/Jakarta') : now('Asia/Jakarta');
        $targetDate = $dispensasiDate ? $dispensasiDate->copy()->setTimezone('Asia/Jakarta') : $now;

        // Jika tanggal dispensasi adalah hari sebelum hari ini (kemarin, dst), KBM-nya sudah pasti selesai
        if ($targetDate->toDateString() < $now->toDateString()) {
            return true;
        }

        // Jika tanggal dispensasi di masa depan (tidak valid untuk auto-complete)
        if ($targetDate->toDateString() > $now->toDateString()) {
            return false;
        }

        // Dispensasi pada hari ini: bandingkan dengan waktu selesai KBM terakhir hari ini
        $waktuSelesai = self::getWaktuSelesaiKbmTerakhir($targetDate->dayOfWeek, $targetDate);
        if (! $waktuSelesai) {
            return false;
        }

        return $now->greaterThanOrEqualTo($waktuSelesai);
    }
}
