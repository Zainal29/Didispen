<?php

namespace App\Console\Commands;

use App\Models\WhatsappLog;
use App\Services\WhatsAppBot;
use App\Services\WhatsappMessageService;
use Illuminate\Console\Command;

class TestKirimWa extends Command
{
    /**
     * Signature command:
     * php artisan wa:test
     * php artisan wa:test 6288983239630
     * php artisan wa:test 6288983239630 --message="Pesan kustom"
     * php artisan wa:test 6288983239630 --async
     */
    protected $signature = 'wa:test 
                            {phone=6288983239630 : Nomor WhatsApp tujuan (format 628... / 08...)} 
                            {--message= : Isi pesan khusus yang ingin dikirimkan}
                            {--async : Gunakan antrean SiPintu (async) daripada pengiriman instan (sync)}';

    protected $description = 'Uji coba pengiriman pesan WhatsApp melalui SiPintu Gateway / Bot WhatsApp DiDispen';

    public function handle(): int
    {
        $rawPhone = (string) $this->argument('phone');
        $phone = WhatsappMessageService::formatPhoneNumber($rawPhone);

        $this->info('====================================================');
        $this->info('  🚀 UJI COBA PENGIRIMAN WHATSAPP BOT DIDISPEN');
        $this->info('====================================================');

        if (! $phone) {
            $this->error("❌ Nomor tujuan '{$rawPhone}' tidak valid!");
            $this->line("   Pastikan nomor diawali '08...' atau '628...' dengan panjang 10-14 digit.");
            return self::FAILURE;
        }

        $this->line("📱 Nomor Tujuan     : <fg=green;options=bold>{$phone}</>");
        $this->line("⚙️  SiPintu Client ID : " . (config('services.sipintu.client_id') ?: '(belum dikonfigurasi)'));

        // Cek status koneksi gateway
        $this->line("🔍 Memeriksa status WhatsApp Gateway SiPintu...");
        $status = WhatsAppBot::status();
        if ($status['success']) {
            $gw = $status['data']['whatsapp_gateway'] ?? [];
            $isConnected = (bool) ($gw['is_connected'] ?? false);
            $assigned = $gw['assigned_phone'] ?? 'Belum ada';
            $session = $gw['session_id'] ?? '-';
            $this->line("   Status Sesi : " . ($isConnected ? '<fg=green>🟢 Terhubung (Online)</>' : '<fg=yellow>🟡 Belum Terhubung</>'));
            $this->line("   Session ID  : {$session}");
            $this->line("   Nomor Bot   : +{$assigned}");
        } else {
            $this->warn("⚠️  Status Gateway: " . ($status['error'] ?? 'Tidak dapat mengecek'));
        }

        $customMessage = $this->option('message');
        $message = $customMessage ?: "Halo! Ini adalah pesan uji coba dari CLI DIDISPEN SMKN 1 Bangsri pada " . now('Asia/Jakarta')->translatedFormat('l, d F Y H:i:s') . " WIB.";

        $sync = ! $this->option('async');
        $modeStr = $sync ? 'Instan (Sync direct socket)' : 'Antrean SiPintu (Async)';
        $this->line("📨 Mode Pengiriman  : <fg=cyan>{$modeStr}</>");
        $this->line("💬 Isi Pesan        : {$message}");
        $this->newLine();

        $this->line("⏳ Sedang mengirimkan pesan...");
        $startTime = microtime(true);

        $result = WhatsAppBot::send($phone, $message, sync: $sync);
        $duration = round((microtime(true) - $startTime) * 1000);

        if ($result['success']) {
            $this->info("✅ BERHASIL! Pesan berhasil terkirim dalam {$duration}ms.");
            $this->line("   Detail Respon: " . json_encode($result['data'], JSON_UNESCAPED_SLASHES));

            try {
                WhatsappLog::create([
                    'user_id'        => null,
                    'recipient_name' => "Tes CLI ({$phone})",
                    'phone'          => $phone,
                    'context'        => 'test_cli',
                    'reference_key'  => null,
                    'message'        => $message,
                    'status'         => 'sent',
                    'sent_at'        => now(),
                ]);
            } catch (\Throwable $e) {
                // Jangan gagalkan perintah CLI jika logging database offline
            }

            return self::SUCCESS;
        }

        $this->error("❌ GAGAL! " . ($result['error'] ?? 'Terjadi kesalahan tidak diketahui.'));
        $this->line("   Detail: " . json_encode($result, JSON_UNESCAPED_SLASHES));

        try {
            WhatsappLog::create([
                'user_id'        => null,
                'recipient_name' => "Tes CLI ({$phone})",
                'phone'          => $phone,
                'context'        => 'test_cli',
                'reference_key'  => null,
                'message'        => $message,
                'status'         => 'failed',
                'error_message'  => $result['error'] ?? 'Gagal dari API',
            ]);
        } catch (\Throwable $e) {
            // Abaikan
        }

        return self::FAILURE;
    }
}
