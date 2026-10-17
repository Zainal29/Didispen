<?php

namespace App\Jobs;

use App\Models\WhatsappLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendBaileysWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $logId) {}

    public function handle(): void
    {
        $log = WhatsappLog::find($this->logId);
        if (! $log || $log->status === 'sent') {
            return;
        }

        if (empty($log->phone)) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Nomor telepon pengguna kosong atau tidak valid.',
            ]);
            return;
        }

        $botUrl = rtrim(\App\Models\Setting::get('wa_bot_url', config('services.whatsapp_bot.url', 'http://127.0.0.1:3789')), '/');
        $apiKey = \App\Models\Setting::get('wa_bot_api_key', config('services.whatsapp_bot.api_key', 'sipintu_wa_secret_key_2026'));

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept'    => 'application/json',
            ])
            ->timeout(15)
            ->post("{$botUrl}/send-message", [
                'phone'   => $log->phone,
                'message' => $log->message,
            ]);

            if ($response->successful()) {
                $log->update([
                    'status'        => 'sent',
                    'sent_at'       => now(),
                    'error_message' => null,
                ]);
            } else {
                $log->update([
                    'status'        => 'failed',
                    'error_message' => 'HTTP ' . $response->status() . ': ' . $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $log->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e; // Lempar ulang agar Queue meretry sesuai --tries=3
        }
    }
}