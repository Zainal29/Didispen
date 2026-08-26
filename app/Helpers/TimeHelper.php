<?php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Mengubah teks "Jam Pelajaran ke-X" menjadi waktu aktual
     * Berdasarkan PDF Jadwal KBM SMKN 1 Bangsri T.A. 2026/2027:
     *   Jam 1-3  : 07:00 - 09:15
     *   Istirahat 1 (antara jam 3 & 4) : 09:15 - 09:30
     *   Jam 4-6  : 09:30 - 11:45
     *   Istirahat 2 (antara jam 6 & 7) : 11:45 - 12:15
     *   Jam 7-10 : 12:15 - 15:15
     */
    public static function getWaktuAktual($teksJam)
    {
        // 1. Ambil angka dari teks (misal: "Jam Pelajaran ke-5" menjadi 5)
        preg_match('/ke-(\d+)/', $teksJam, $matches);
        $angka = isset($matches[1]) ? (int) $matches[1] : 0;

        // 2. Jadwal pelajaran sesuai PDF Jadwal KBM SMKN 1 Bangsri
        $jadwal = [
            1 => '07:00 - 07:45',
            2 => '07:45 - 08:30',
            3 => '08:30 - 09:15',
            4 => '09:30 - 10:15',  // Setelah istirahat pertama (09:15-09:30)
            5 => '10:15 - 11:00',
            6 => '11:00 - 11:45',
            7 => '12:15 - 13:00',  // Setelah istirahat kedua (11:45-12:15)
            8 => '13:00 - 13:45',
            9 => '13:45 - 14:30',
            10 => '14:30 - 15:15',
        ];

        // 3. Kembalikan waktu, jika angka tidak ditemukan kembalikan '-'
        return $jadwal[$angka] ?? '-';
    }
}
