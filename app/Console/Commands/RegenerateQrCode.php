<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dispensasi;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            ->with(['siswa.kelas', 'guru'])
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

                $dispensasi->qr_token ??= Str::random(64);
                $qrData = ['token' => $dispensasi->qr_token];

                $qrCodePath = 'qr_codes/dispensasi_' . $dispensasi->id . '.svg';

                Storage::disk('public')->put(
                    $qrCodePath,
                    QrCode::format('svg')->size(300)->generate(json_encode($qrData))
                );

                $dispensasi->update([
                    'qr_code' => $qrCodePath,
                    'qr_token' => $dispensasi->qr_token,
                ]);

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