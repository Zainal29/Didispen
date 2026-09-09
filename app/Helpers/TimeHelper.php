<?php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Mengubah teks "Jam Pelajaran ke-X" menjadi waktu aktual
     * Berdasarkan PDF Jadwal KBM SMKN 1 Bangsri T.A. 2026/2027
     *
     * @param string $teksJam Contoh: "Jam Pelajaran ke-5"
     * @param int|null $dayOfWeek Day of week (0=Minggu, 5=Jumat, 6=Sabtu) - default sekarang
     * @return string Format: "HH:MM - HH:MM"
     */
    public static function getWaktuAktual($teksJam, $dayOfWeek = null)
    {
        // 1. Ambil angka dari teks (misal: "Jam Pelajaran ke-5" menjadi 5)
        preg_match('/ke-(\d+)/', $teksJam, $matches);
        $angka = isset($matches[1]) ? (int) $matches[1] : 0;

        // 2. Jika dayOfWeek tidak diberikan, gunakan hari sekarang
        if ($dayOfWeek === null) {
            $dayOfWeek = now()->dayOfWeek;
        }

        // 3. Jadwal pelajaran sesuai PDF Jadwal KBM SMKN 1 Bangsri
        // Senin-Kamis: 10 jam pelajaran
        $jadwalReguler = [
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

        // Jumat: 8 jam pelajaran (berdasarkan PDF)
        $jadwalJumat = [
            1 => '07:00 - 07:45',
            2 => '07:45 - 08:30',
            3 => '08:30 - 09:15',
            4 => '09:30 - 10:15',  // Setelah istirahat pertama
            5 => '10:15 - 11:00',
            6 => '11:00 - 11:45',
            7 => '12:15 - 13:00',  // Setelah istirahat kedua
            8 => '13:00 - 13:50',  // Jumat berakhir lebih awal
        ];

        // 4. Pilih jadwal berdasarkan hari
        $jadwal = ($dayOfWeek === 5) ? $jadwalJumat : $jadwalReguler;

        // 5. Kembalikan waktu, jika angka tidak ditemukan kembalikan '-'
        return $jadwal[$angka] ?? '-';
    }
}
