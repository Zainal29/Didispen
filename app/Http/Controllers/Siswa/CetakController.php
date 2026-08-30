<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Helpers\PrintHelper;
use App\Models\Dispensasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CetakController extends Controller
{
    public function cetak(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        // 1. Pastikan dispensasi ini milik siswa yang sedang login
        if (!$dispensasi->siswa || $dispensasi->siswa->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mencetak surat ini.');
        }

        // 2. Validasi menggunakan helper khusus SISWA
        if ($reason = PrintHelper::getStudentBlockReason($dispensasi)) {
            return redirect()
                ->route('siswa.pengajuan.show', $dispensasi)
                ->with('error', $reason);
        }

        // 3. Cek limit cetak SISWA
        $maxPrint = PrintHelper::maxStudentLimit();
        $currentCount = $dispensasi->student_print_count ?? 0;

        if ($currentCount >= $maxPrint) {
            return redirect()
                ->route('siswa.pengajuan.show', $dispensasi)
                ->with('error', "Batas cetak Anda telah tercapai ({$maxPrint} kali). Hubungi guru untuk mencetak.");
        }

        // 4. ✅ Increment counter SISWA
        $dispensasi->update([
            'student_print_count' => $currentCount + 1,
            'printed_at' => now(),
        ]);

        // 5. Generate dan download PDF
        $safeNomorSurat = str_replace(['/', '\\'], '-', $dispensasi->nomor_surat);
        $pdf = Pdf::loadView('pdf.surat-dispensasi', compact('dispensasi'));

        return $pdf->download("Surat_Dispensasi_{$safeNomorSurat}.pdf");
    }
}
