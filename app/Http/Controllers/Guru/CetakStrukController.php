<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Helpers\PrintHelper;
use App\Helpers\TimeHelper;
use App\Models\Dispensasi;
use App\Models\Guru;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CetakStrukController extends Controller
{
    /**
     * Cetak Struk Thermal 58mm sebagai PNG
     */
  public function index(Dispensasi $dispensasi, Request $request)
    {
        $dispensasi->load([
            'siswa.user',
            'siswa.kelas.jurusan',
            'guru.user',
        ]);

        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'guru'], true)) {
            abort(403, 'Akses ditolak. Hanya Admin atau Guru yang dapat mencetak.');
        }

        if (!in_array($dispensasi->status, PrintHelper::PRINTABLE_STATUSES, true)) {
            abort(403, 'Dispensasi harus dalam status disetujui untuk dicetak.');
        }

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

        $maxPrint = PrintHelper::maxTeacherLimit();
        $currentTeacherCount = (int) ($dispensasi->teacher_print_count ?? 0);

        if ($currentTeacherCount >= $maxPrint) {
            abort(403, "Batas cetak guru telah tercapai ({$maxPrint} kali).");
        }

        $pdftoppm = '/usr/bin/pdftoppm';
        if (!is_executable($pdftoppm)) {
            abort(500, 'pdftoppm tidak tersedia di server.');
        }

        /*
         * Render Blade thermal ke PDF dengan lebar pas 58mm (164.41pt)
         */
        $pdf = Pdf::loadView('pdf.struk-dispensasi-58mm', compact('dispensasi'));
        $pdf->setPaper([0, 0, 164.41, 600], 'portrait');
        $pdfContent = $pdf->output();

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $uniqueId = uniqid('struk_', true);
        $pdfPath = $tempDir . '/' . $uniqueId . '.pdf';
        $pngPrefix = $tempDir . '/' . $uniqueId;

        file_put_contents($pdfPath, $pdfContent);

        try {
            /*
             * Render pdftoppm pada 203 DPI (Native resolution thermal POS-58)
             * Hasil lebar adalah ~384px - 400px tanpa blur interpolasi.
             */
            $command = sprintf(
                '%s -png -r 203 -singlefile %s %s 2>&1',
                escapeshellarg($pdftoppm),
                escapeshellarg($pdfPath),
                escapeshellarg($pngPrefix)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \RuntimeException('pdftoppm gagal: ' . implode("\n", $output));
            }

            $generatedPng = $pngPrefix . '.png';

            if (!file_exists($generatedPng)) {
                throw new \RuntimeException('File PNG hasil konversi tidak ditemukan.');
            }

            $image = imagecreatefrompng($generatedPng);
            if ($image === false) {
                throw new \RuntimeException('PNG hasil konversi tidak dapat dibaca oleh GD.');
            }

            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            $targetWidth = 384;

            // Jika lebar sudah pas 384px (atau mendekati), jangan resample ganda agar teks tetap tajam
            if (abs($originalWidth - $targetWidth) <= 4) {
                $finalPng = file_get_contents($generatedPng);
                imagedestroy($image);
            } else {
                $targetHeight = (int) round($originalHeight * ($targetWidth / $originalWidth));
                $resized = imagecreatetruecolor($targetWidth, $targetHeight);

                $white = imagecolorallocate($resized, 255, 255, 255);
                imagefill($resized, 0, 0, $white);

                // Pertahankan ketajaman font
                imagecopyresampled(
                    $resized,
                    $image,
                    0, 0, 0, 0,
                    $targetWidth,
                    $targetHeight,
                    $originalWidth,
                    $originalHeight
                );

                ob_start();
                imagepng($resized, null, 0); // Kompresi 0 = tanpa kompresi artefak (sangat tajam)
                $finalPng = ob_get_clean();

                imagedestroy($image);
                imagedestroy($resized);
            }

            @unlink($pdfPath);
            @unlink($generatedPng);

            $dispensasi->update([
                'teacher_print_count' => $currentTeacherCount + 1,
                'printed_at' => now(),
            ]);

            return response($finalPng)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="struk-' . $dispensasi->nomor_surat . '.png"')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');

        } catch (\Throwable $e) {
            @unlink($pdfPath);
            @unlink($pngPrefix . '.png');

            \Log::error('Gagal membuat PNG struk thermal', [
                'dispensasi_id' => $dispensasi->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Gagal membuat PNG struk: ' . $e->getMessage());
        }
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