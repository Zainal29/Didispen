<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;

class VerifikasiController extends Controller
{
    public function show(string $nomorSurat)
    {
        $dispensasi = Dispensasi::with(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru'])
            ->where('nomor_surat', $nomorSurat)
            ->firstOrFail();

        return view('verifikasi.show', compact('dispensasi'));
    }
}