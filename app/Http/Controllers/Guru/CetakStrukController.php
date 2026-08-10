<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;

class CetakStrukController extends Controller
{
    public function index(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

        // Otorisasi: hanya guru piket yang bersangkutan atau admin
        if (auth()->user()->role !== 'admin') {
            if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru?->id) {
                abort(403, 'Anda tidak berhak mencetak dispensasi ini.');
            }
        }

        // Hanya dispensasi yang sudah disetujui yang bisa dicetak
        if (!in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai'])) {
            abort(403, 'Dispensasi harus dalam status disetujui untuk dicetak.');
        }

        return view('guru.cetak-struk', compact('dispensasi'));
    }
}
