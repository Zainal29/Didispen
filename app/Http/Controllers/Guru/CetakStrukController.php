<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Helpers\PrintHelper;
use App\Models\Dispensasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CetakStrukController extends Controller
{
    public function index(Dispensasi $dispensasi, Request $request)
    {
        return $this->exportPdf($dispensasi, $request);
    }

    /**
     * Export / Stream PDF Struk Dispensasi (Format Thermal 58mm / A4)
     */
    public function exportPdf(Dispensasi $dispensasi, Request $request)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guru']);

        // Otorisasi: hanya guru piket yang bersangkutan atau admin
        if (auth()->user()->role !== 'admin') {
            if (!$dispensasi->guru_id || $dispensasi->guru_id !== auth()->user()->guru?->id) {
                abort(403, 'Anda tidak berhak mencetak dispensasi ini.');
            }
        }

        // Hanya dispensasi yang sudah disetujui / keluar / selesai yang bisa dicetak
        if (!in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai'])) {
            abort(403, 'Dispensasi harus dalam status disetujui untuk dicetak.');
        }

        // ✅ Cek limit cetak GURU
        $maxPrint = PrintHelper::maxTeacherLimit();
        $currentTeacherCount = $dispensasi->teacher_print_count ?? 0;
        if ($currentTeacherCount >= $maxPrint) {
            abort(403, "Batas cetak guru telah tercapai ({$maxPrint} kali).");
        }

        $format = $request->query('format', 'thermal');

        // ✅ Increment counter GURU (bukan print_count umum!)
        $dispensasi->update([
            'teacher_print_count' => $currentTeacherCount + 1,
            'printed_at' => now(),
        ]);

        $safeNomorSurat = str_replace(['/', '\\'], '-', $dispensasi->nomor_surat);

        if ($format === 'a4') {
            $pdf = Pdf::loadView('pdf.surat-dispensasi', compact('dispensasi'));
            return $pdf->stream("Surat_Dispensasi_{$safeNomorSurat}.pdf");
        }

        // Generate QR Base64 Image untuk DomPDF
        $qrBase64 = null;
        if (!empty($dispensasi->qr_code)) {
            $filePath = storage_path('app/public/' . $dispensasi->qr_code);
            if (file_exists($filePath)) {
                $mime = mime_content_type($filePath) ?: 'image/png';
                $qrBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
            }
        }

        if (!$qrBase64) {
            $qrContent = url('/verifikasi/' . $dispensasi->id);
            if (class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                $svg = QrCode::size(120)->margin(0)->generate($qrContent);
                $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }

        // Lebar Kertas 58mm = 164.41 pt. Set Canvas [0, 0, 164.41, 480]
        $pdf = Pdf::loadView('pdf.struk-dispensasi-58mm', compact('dispensasi', 'qrBase64'))
            ->setPaper([0, 0, 164.41, 480], 'portrait');

        return $pdf->stream("Struk_Dispensasi_58mm_{$safeNomorSurat}.pdf");
    }
}
