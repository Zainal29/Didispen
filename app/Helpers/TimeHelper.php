<?php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Mengubah teks "Jam Pelajaran ke-X" menjadi waktu aktual
     * Fungsi ini akan dibaca oleh Siswa, Guru, dan Admin secara sinkron.
     */
    public static function getWaktuAktual($teksJam)
    {
        // 1. Ambil angka dari teks (misal: "Jam Pelajaran ke-5" menjadi 5)
        preg_match('/ke-(\d+)/', $teksJam, $matches);
        $angka = isset($matches[1]) ? (int)$matches[1] : 0;

        // 2. Tentukan jadwal pelajaran (SESUAIKAN DENGAN JADWAL SEKOLAH ANDA)
        $jadwal = [
            1  => '07:00 - 07:45',
            2  => '07:45 - 08:30',
            3  => '08:30 - 09:15',
            4  => '09:15 - 10:00',
            5  => '10:00 - 10:45',
            6  => '10:45 - 11:30',
            7  => '11:30 - 12:15',
            8  => '12:15 - 13:00',
            9  => '13:00 - 13:45',
            10 => '13:45 - 14:30',
        ];

        // 3. Kembalikan waktu, jika angka tidak ditemukan kembalikan '-'
        return $jadwal[$angka] ?? '-';
    }
}