<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Helpers\PrintHelper;
use App\Models\Dispensasi;
use App\Models\Guru;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CetakController extends Controller
{
    public function cetak(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru.user', 'anggotaSiswa.kelas.jurusan', 'anggotaSiswa.user']);

        $userSiswa = Auth::user()->siswa;
        $isOwner = $dispensasi->siswa_id === $userSiswa?->id;
        $isAnggota = $userSiswa && $dispensasi->anggotaSiswa()->where('siswa.id', $userSiswa->id)->exists();

        // 1. Pastikan dispensasi ini milik siswa atau siswa adalah anggota rombongan
        if (! $isOwner && ! $isAnggota) {
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

        // ✅ Self-healing: jika guru_id belum tersimpan (misal disetujui sebelum patch), kaitkan dengan guru aktif
        if (empty($dispensasi->guru_id)) {
            $fallbackGuru = Guru::where('status_aktif', true)->first();
            if ($fallbackGuru) {
                $dispensasi->update(['guru_id' => $fallbackGuru->id]);
                $dispensasi->load('guru.user');
            }
        }

        // 4. Increment counter SISWA
        $dispensasi->update([
            'student_print_count' => $currentCount + 1,
            'printed_at' => now(),
        ]);

        // 5. Generate dan download PDF
        $dispensasi->load(['anggotaSiswa', 'siswa.kelas.jurusan', 'guru']);
        $safeNomorSurat = str_replace(['/', '\\'], '-', $dispensasi->nomor_surat);
        $pdf = Pdf::loadView('pdf.surat-dispensasi', compact('dispensasi'));

        return $pdf->download("Surat_Dispensasi_{$safeNomorSurat}.pdf");
    }
}
