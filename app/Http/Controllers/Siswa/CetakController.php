<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Support\Facades\Auth;

class CetakController extends Controller
{
    public function cetak(Dispensasi $dispensasi)
    {
        // 1. Load semua relasi di awal agar PDF tidak error "property on null"
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

        // 2. Pastikan dispensasi ini milik siswa yang sedang login
        if (!$dispensasi->siswa || $dispensasi->siswa->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mencetak surat ini.');
        }

        // 3. Hanya bisa cetak jika status sudah disetujui atau selesai
        if (!in_array($dispensasi->status, ['disetujui', 'selesai'])) {
            return redirect()->back()->with('error', 'Surat hanya bisa dicetak setelah mendapatkan persetujuan dari guru piket.');
        }

        // 4. Batasi maksimal cetak sesuai batas yang ditentukan di database
        if ($dispensasi->print_count >= $dispensasi->max_print_limit) {
            return redirect()->back()->with('error', "Batas maksimal cetak surat telah tercapai ({$dispensasi->max_print_limit} kali). Silakan hubungi admin jika membutuhkan cetak ulang.");
        }

        // 5. Tambah counter cetak dan catat waktu cetak
        $dispensasi->update([
            'print_count' => $dispensasi->print_count + 1,
            'printed_at' => now(),
        ]);

        // 6. ✅ PERBAIKAN PENTING: Sanitasi nomor surat agar tidak mengandung '/' atau '\'
        $safeNomorSurat = str_replace(['/', '\\'], '-', $dispensasi->nomor_surat);
        $filename = "Surat_Dispensasi_{$safeNomorSurat}.pdf";

        // 7. Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat-dispensasi', compact('dispensasi'));

        // 8. Download file PDF dengan nama yang sudah aman
        return $pdf->download($filename);
    }
}