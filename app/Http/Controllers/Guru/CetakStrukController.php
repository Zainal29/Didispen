<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Helpers\PrintHelper;
use App\Models\Dispensasi;
use App\Models\Guru;
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
        $dispensasi->load([
            'siswa.user',
            'siswa.kelas.jurusan',
            'guru.user',
        ]);

        $user = auth()->user();

        // Otorisasi: hanya Admin atau Guru
        if (!in_array($user->role, ['admin', 'guru'], true)) {
            abort(
                403,
                'Akses ditolak. Hanya Admin atau Guru yang dapat mencetak.'
            );
        }

        // Hanya status yang dapat dicetak
        if (!in_array(
            $dispensasi->status,
            PrintHelper::PRINTABLE_STATUSES,
            true
        )) {
            abort(
                403,
                'Dispensasi harus dalam status disetujui untuk dicetak.'
            );
        }

        // Self-healing guru_id jika belum tersedia
        if (empty($dispensasi->guru_id)) {
            if ($user->guru) {
                $dispensasi->update([
                    'guru_id' => $user->guru->id,
                ]);

                $dispensasi->load('guru.user');
            } else {
                $fallbackGuru = Guru::where('status_aktif', true)->first();

                if ($fallbackGuru) {
                    $dispensasi->update([
                        'guru_id' => $fallbackGuru->id,
                    ]);

                    $dispensasi->load('guru.user');
                }
            }
        }

        // Cek limit cetak guru
        $maxPrint = PrintHelper::maxTeacherLimit();
        $currentTeacherCount = (int) (
            $dispensasi->teacher_print_count ?? 0
        );

        if ($currentTeacherCount >= $maxPrint) {
            abort(
                403,
                "Batas cetak guru telah tercapai ({$maxPrint} kali)."
            );
        }

        $format = $request->query('format', 'thermal');

        $safeNomorSurat = str_replace(
            ['/', '\\'],
            '-',
            $dispensasi->nomor_surat
        );

        /*
         * A4
         *
         * Render PDF terlebih dahulu.
         * Counter hanya bertambah jika PDF berhasil dirender.
         */
        if ($format === 'a4') {
            $pdf = Pdf::loadView(
                'pdf.surat-dispensasi',
                compact('dispensasi')
            );

            $pdfContent = $pdf->output();

            $dispensasi->update([
                'teacher_print_count' => $currentTeacherCount + 1,
                'printed_at' => now(),
            ]);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header(
                    'Content-Disposition',
                    'inline; filename="Surat_Dispensasi_' .
                    $safeNomorSurat .
                    '.pdf"'
                );
        }

        /*
         * Generate QR Base64 untuk DomPDF.
         */
        $qrBase64 = null;

        if (!empty($dispensasi->qr_code)) {
            $filePath = storage_path(
                'app/public/' . $dispensasi->qr_code
            );

            if (file_exists($filePath)) {
                $mime = mime_content_type($filePath) ?: 'image/png';

                $qrBase64 =
                    'data:' .
                    $mime .
                    ';base64:' .
                    base64_encode(
                        file_get_contents($filePath)
                    );
            }
        }

        /*
         * Fallback QR jika file QR tidak tersedia.
         */
        if (!$qrBase64) {
            $qrContent = url(
                '/verifikasi/' . $dispensasi->id
            );

            if (class_exists(
                '\\SimpleSoftwareIO\\QrCode\\Facades\\QrCode'
            )) {
                $svg = QrCode::size(120)
                    ->margin(0)
                    ->generate($qrContent);

                $qrBase64 =
                    'data:image/svg+xml;base64,' .
                    base64_encode($svg);
            }
        }

        /*
         * Thermal 58mm.
         *
         * Render PDF terlebih dahulu.
         * Jika Blade/DomPDF gagal, counter tidak berubah.
         */
        $pdf = Pdf::loadView(
            'pdf.struk-dispensasi-58mm',
            compact('dispensasi', 'qrBase64')
        )->setPaper(
            [0, 0, 164.41, 480],
            'portrait'
        );

        $pdfContent = $pdf->output();

        /*
         * Counter hanya dinaikkan setelah PDF berhasil
         * dirender sepenuhnya.
         */
        $dispensasi->update([
            'teacher_print_count' => $currentTeacherCount + 1,
            'printed_at' => now(),
        ]);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="Struk_Dispensasi_58mm_' .
                $safeNomorSurat .
                '.pdf"'
            );
    }
}