<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dispensasi;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;

class RegenerateQrCode extends Command
{
    protected $signature = 'dispensasi:regenerate-qr';
    protected $description = 'Regenerate QR Code untuk dispensasi yang sudah disetujui tapi belum punya QR';

    public function handle()
    {
        $this->info('🔍 Mencari dispensasi yang perlu di-regenerate QR Code...');

        // Cari dispensasi yang statusnya disetujui/keluar/selesai tapi qr_code NULL
        $dispensasiList = Dispensasi::whereIn('status', ['disetujui', 'keluar', 'selesai'])
            ->where(function($query) {
                $query->whereNull('qr_code')
                      ->orWhere('qr_code', '');
            })
            ->with(['siswa.kelas', 'guruPiket.guru'])
            ->get();

        if ($dispensasiList->count() === 0) {
            $this->info('✅ Tidak ada dispensasi yang perlu di-regenerate.');
            return 0;
        }

        $this->info(" Ditemukan {$dispensasiList->count()} dispensasi yang perlu di-regenerate.");

        // Pastikan folder ada
        if (!Storage::disk('public')->exists('qr_codes')) {
            Storage::disk('public')->makeDirectory('qr_codes');
            $this->info("📁 Folder qr_codes dibuat.");
        }

        $success = 0;
        $failed = 0;

        foreach ($dispensasiList as $dispensasi) {
            try {
                $this->line("\n🔄 Processing: {$dispensasi->nomor_surat}...");

                $qrData = [
                    'id' => $dispensasi->id,
                    'nomor_surat' => $dispensasi->nomor_surat,
                    'siswa' => $dispensasi->siswa->nama_lengkap,
                    'kelas' => $dispensasi->siswa->kelas->nama_kelas,
                    'jam_keluar' => $dispensasi->jam_keluar,
                    'jam_kembali' => $dispensasi->jam_kembali,
                    'berlaku_sampai' => now()->endOfDay()->toIso8601String(),
                    'token' => md5($dispensasi->id . $dispensasi->nomor_surat . 'SECRET_KEY_DISPENSI')
                ];

                $qrCodePath = 'qr_codes/dispensasi_' . $dispensasi->id . '.svg';

                Storage::disk('public')->put(
                    $qrCodePath,
                    QrCode::format('svg')->size(300)->generate(json_encode($qrData))
                );

                $dispensasi->update(['qr_code' => $qrCodePath]);

                $this->info("✅ {$dispensasi->nomor_surat} - QR Code berhasil dibuat");
                $success++;

            } catch (\Exception $e) {
                Log::error("Gagal regenerate QR untuk {$dispensasi->nomor_surat}: " . $e->getMessage());
                $this->error("❌ {$dispensasi->nomor_surat} - Gagal: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info(' SELESAI!');
        $this->info("✅ Berhasil: {$success}");
        $this->info("❌ Gagal: {$failed}");
        $this->info('═══════════════════════════════════════');

        return 0;
    }
}
