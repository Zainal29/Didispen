<?php

namespace App\Console\Commands;

use App\Models\Dispensasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UpdateQrCodes extends Command
{
    protected $signature = 'qr:update-all';
    protected $description = 'Update semua QR Code ke format URL';

    public function handle()
    {
        $this->info(' Updating all QR Codes to URL format...');

        $dispensasis = Dispensasi::whereIn('status', ['disetujui', 'keluar', 'selesai'])
            ->whereNotNull('qr_token')
            ->get();

        if ($dispensasis->count() === 0) {
            $this->error('Tidak ada dispensasi yang perlu di-update.');
            return 1;
        }

        $bar = $this->output->createProgressBar($dispensasis->count());
        $bar->start();

        foreach ($dispensasis as $d) {
            $qrContent = url('/verify-qr/' . $d->id . '?token=' . $d->qr_token);
            $path = 'qr_codes/dispensasi_' . $d->id . '.svg';

            Storage::disk('public')->makeDirectory('qr_codes');

            QrCode::format('svg')
                ->size(300)
                ->margin(0)
                ->generate($qrContent, storage_path('app/public/' . $path));

            $d->update(['qr_code' => $path]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Selesai! {$dispensasis->count()} QR Code berhasil di-update.");

        return 0;
    }
}
