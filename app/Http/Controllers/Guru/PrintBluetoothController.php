<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrintBluetoothController extends Controller
{
    public function print(Dispensasi $dispensasi)
    {
        $dispensasi->load(['siswa.user', 'siswa.kelas.jurusan', 'guruPiket.guru']);

        // ===== 1. OTORISASI =====
        if (auth()->user()->role !== 'admin') {
            if (!$dispensasi->guruPiket || $dispensasi->guruPiket->guru_id !== auth()->user()->guru?->id) {
                abort(403, 'Anda tidak berhak mencetak dispensasi ini.');
            }
        }

        if (!in_array($dispensasi->status, ['disetujui', 'keluar', 'selesai'])) {
            return redirect()->back()->with('error', 'Dispensasi harus disetujui dulu sebelum dicetak.');
        }

        try {
            $printer = new Printer($this->createConnector());

            // ===== 2. HEADER =====
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("SMK NEGERI 1 BANGSRI\n");
            $printer->setTextSize(1, 1);
            $printer->text("SURAT DISPENSASI\n");
            $printer->text($dispensasi->nomor_surat . "\n");
            $printer->text(str_repeat("=", 32) . "\n");

            // ===== 3. DATA SISWA (REAL-TIME) =====
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Nama  : " . $dispensasi->siswa->nama_lengkap . "\n");
            $printer->text("NIS   : " . ($dispensasi->siswa->user->nis_nip ?? '-') . "\n");
            $printer->text("Kelas : " . $dispensasi->siswa->kelas->nama_kelas
                . " - " . ($dispensasi->siswa->kelas->jurusan->nama_jurusan ?? '-') . "\n");
            $printer->text(str_repeat("-", 32) . "\n");

            // ===== 4. DETAIL DISPENSASI (REAL-TIME) =====
            $printer->text("Kategori: " . ucfirst(str_replace('_', ' ', $dispensasi->kategori)) . "\n");
            $printer->text("Alasan  : " . $dispensasi->alasan . "\n");
            $printer->text("Tujuan  : " . $dispensasi->tujuan . "\n");
            if ($dispensasi->lokasi) {
                $printer->text("Lokasi  : " . $dispensasi->lokasi . "\n");
            }
            $printer->text("Keluar  : " . $dispensasi->jam_keluar . "\n");
            $printer->text("Kembali : " . $dispensasi->jam_kembali . "\n");

            // ✅ Tambahkan info waktu cetak real-time
            $printer->text("Dicetak : " . now()->format('d/m/Y H:i:s') . "\n");
            $printer->text(str_repeat("-", 32) . "\n");

            // ===== 5. QR CODE REAL-TIME =====
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\n[ QR CODE VALIDASI ]\n");

            // ✅ Generate data QR Code dengan timestamp REAL-TIME (bukan dari waktu approve)
            $qrDataRealtime = json_encode([
                'id' => $dispensasi->id,
                'nomor_surat' => $dispensasi->nomor_surat,
                'siswa' => $dispensasi->siswa->nama_lengkap,
                'kelas' => $dispensasi->siswa->kelas->nama_kelas,
                'jam_keluar' => $dispensasi->jam_keluar,
                'jam_kembali' => $dispensasi->jam_kembali,
                'status' => $dispensasi->status,
                'dicetak_pada' => now()->toIso8601String(), // ✅ Timestamp real-time saat cetak
                'berlaku_sampai' => now()->endOfDay()->toIso8601String(), // ✅ Diupdate saat cetak
                'token' => md5($dispensasi->id . $dispensasi->nomor_surat . now()->format('Ymd') . 'SECRET_KEY_DISPENSI')
            ], JSON_UNESCAPED_SLASHES);

            try {
                // ✅ Print QR Code dengan data real-time
                // Ukuran 6 = optimal untuk kertas 58mm (cukup besar untuk di-scan)
                $printer->qrCode($qrDataRealtime, Printer::QR_ECLEVEL_M, 6);
                $printer->text("\nScan di Pos Satpam\n");
            } catch (\Exception $e) {
                Log::warning('QR Code native gagal, fallback ke teks: ' . $e->getMessage());
                $printer->text("No: " . $dispensasi->nomor_surat . "\n");
                $printer->text("Verifikasi manual\n");
            }

            // ===== 6. TANDA TANGAN =====
            $guru = $dispensasi->guruPiket?->guru;
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\nGuru Piket,\n\n\n\n");
            $printer->setEmphasis(true);
            $printer->text(($guru->nama_lengkap ?? '..........................') . "\n");
            $printer->setEmphasis(false);
            if (!empty($guru->nip)) {
                $printer->text("NIP. " . $guru->nip . "\n");
            }

            // ===== 7. FOOTER =====
            $printer->text(str_repeat("=", 32) . "\n");
            $printer->text("Struk ini sah dan ditandatangani\nsecara elektronik\n");
            $printer->text("Dicetak: " . now()->format('d/m/Y H:i:s') . " WIB\n");
            $printer->text("SMK N 1 Bangsri - Jepara\n");

            // ===== 8. SELESAI =====
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            // ✅ Update counter cetak di database (real-time tracking)
            $dispensasi->increment('print_count');
            $dispensasi->update(['printed_at' => now()]);

            return redirect()->back()->with('success', 'Struk berhasil dicetak dengan QR Code real-time!');

        } catch (\Exception $e) {
            Log::error('Print thermal gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencetak: ' . $e->getMessage());
        }
    }

    // ===== 9. CONNECTOR PRINTER =====
    private function createConnector()
    {
        $type = env('PRINTER_CONNECTION', 'auto');
        $name = env('PRINTER_NAME', 'POS-5809DD');
        $dev  = env('PRINTER_DEVICE', '/dev/rfcomm0');
        $host = env('PRINTER_NETWORK_HOST');
        $port = (int) env('PRINTER_NETWORK_PORT', 9100);

        if ($type === 'auto') {
            $type = match (PHP_OS_FAMILY) {
                'Windows' => 'windows',
                'Darwin'  => 'cups',
                default   => (file_exists($dev) && is_writable($dev)) ? 'file' : 'cups',
            };
        }

        return match ($type) {
            'file'    => new FilePrintConnector($dev),
            'cups'    => new CupsPrintConnector($name),
            'windows' => new WindowsPrintConnector($name),
            'network' => new NetworkPrintConnector($host, $port),
            default   => throw new \Exception("PRINTER_CONNECTION tidak dikenal: $type"),
        };
    }
}