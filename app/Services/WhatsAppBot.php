<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBot
{
    /**
     * Kirim pesan WhatsApp via SiPintu Gateway
     * 
     * @param string $phone Nomor tujuan (format 0812... atau 62812...)
     * @param string $message Isi pesan WhatsApp
     * @param bool $sync true = kirim langsung via socket WA (200 OK, rekomendasi), false = antrean background SiPintu (202 Accepted)
     * @return array ['success' => bool, 'data' => ?array, 'error' => ?string]
     */
    public static function send(string $phone, string $message, bool $sync = true): array
    {
        $baseUrl = rtrim((string) (config('services.sipintu.url') ?: config('services.sipintu.api_url') ?: 'https://sipintu.smkn1bangsri.sch.id'), '/');
        $url = $baseUrl . '/api/v1/whatsapp/send';
        $clientId = config('services.sipintu.client_id');
        $clientSecret = config('services.sipintu.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'error'   => 'Kredensial SiPintu Gateway (SIPINTU_CLIENT_ID / SIPINTU_CLIENT_SECRET) belum dikonfigurasi.',
            ];
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone)) {
            return [
                'success' => false,
                'error'   => 'Nomor telepon tujuan kosong atau mengandung format yang tidak valid.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-Client-ID'     => $clientId,
                'X-Client-Secret' => $clientSecret,
                'Accept'          => 'application/json',
                'Content-Type'    => 'application/json',
            ])->timeout(20)->post($url, [
                'phone'   => $cleanPhone,
                'message' => $message,
                'sync'    => $sync,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            $errorMessage = $response->json('message') ?? ('Pengiriman via SiPintu gagal (HTTP ' . $response->status() . ').');
            Log::error('[WhatsAppBot] Gagal kirim via SiPintu: ' . $response->body());

            return [
                'success' => false,
                'error'   => $errorMessage,
            ];
        } catch (\Throwable $e) {
            Log::error('[WhatsAppBot] Exception saat menghubungi SiPintu Gateway: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => 'Gagal terhubung ke SiPintu WhatsApp Gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ambil status nomor WhatsApp bot aplikasi di SiPintu Gateway
     */
    public static function status(): array
    {
        $baseUrl = rtrim((string) (config('services.sipintu.url') ?: config('services.sipintu.api_url') ?: 'https://sipintu.smkn1bangsri.sch.id'), '/');
        $url = $baseUrl . '/api/v1/whatsapp/status';
        $clientId = config('services.sipintu.client_id');
        $clientSecret = config('services.sipintu.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return [
                'success' => false,
                'error'   => 'Kredensial SiPintu Gateway belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'X-Client-ID'     => $clientId,
                'X-Client-Secret' => $clientSecret,
                'Accept'          => 'application/json',
            ])->connectTimeout(3)->timeout(5)->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error'   => $response->json('message') ?? ('HTTP ' . $response->status()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Cek cepat apakah nomor bot aplikasi sedang aktif terhubung di SiPintu
     */
    public static function isConnected(): bool
    {
        $status = self::status();
        return (bool) ($status['data']['whatsapp_gateway']['is_connected'] ?? false);
    }
}
