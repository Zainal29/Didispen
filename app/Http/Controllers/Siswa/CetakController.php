<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Helpers\PrintHelper;
use App\Models\Dispensasi;
use Illuminate\Support\Facades\Auth;

class CetakController extends Controller
{
    public function cetak(Dispensasi $dispensasi)
    {
        // 1. Load semua relasi di awal agar PDF tidak error "property on null"
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        // 2. Pastikan dispensasi ini milik siswa yang sedang login
        if (!$dispensasi->siswa || $dispensasi->siswa->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mencetak surat ini.');
        }

        // 3. Validasi konsisten dengan panel Guru (via App\Helpers\PrintHelper):
        //    status (disetujui/keluar/selesai), batas cetak GLOBAL dari settings,
        //    dan jam operasional cetak.
        if ($reason = PrintHelper::blockReason($dispensasi)) {
            return redirect()
                ->route('siswa.pengajuan.show', $dispensasi)
                ->with('error', $reason);
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