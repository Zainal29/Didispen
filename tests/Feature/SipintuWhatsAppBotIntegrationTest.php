<?php

namespace Tests\Feature;

use App\Jobs\SendBaileysWhatsAppJob;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\WhatsAppBot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SipintuWhatsAppBotIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sipintu.url'           => 'https://sipintu.smkn1bangsri.sch.id',
            'services.sipintu.client_id'     => 'app_test_client_123',
            'services.sipintu.client_secret' => 'sec_test_secret_456',
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@test.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'nis_nip'  => 'ADM001',
        ]);
    }

    public function test_whatsapp_bot_service_sends_message_via_sipintu_gateway(): void
    {
        Http::fake([
            '*/api/v1/whatsapp/send' => Http::response([
                'status'  => 'queued',
                'message' => 'Pesan WhatsApp berhasil dimasukkan ke dalam antrean pengiriman.',
                'data'    => [
                    'log_id'     => 99,
                    'recipient'  => '628123456789',
                    'session_id' => 'app_didispen',
                ],
            ], 202),
        ]);

        $result = WhatsAppBot::send('08123456789', 'Halo ini pesan tes via SiPintu');

        $this->assertTrue($result['success']);
        $this->assertEquals('queued', $result['data']['status']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/whatsapp/send')
                && $request->header('X-Client-ID')[0] === 'app_test_client_123'
                && $request->header('X-Client-Secret')[0] === 'sec_test_secret_456'
                && $request['phone'] === '08123456789';
        });
    }

    public function test_whatsapp_bot_service_checks_status_via_sipintu_gateway(): void
    {
        Http::fake([
            '*/api/v1/whatsapp/status' => Http::response([
                'status' => 'success',
                'application' => [
                    'name'      => 'DiDispen',
                    'slug'      => 'didispen',
                    'client_id' => 'app_test_client_123',
                ],
                'whatsapp_gateway' => [
                    'session_id'               => 'app_didispen',
                    'assigned_phone'           => '6281234567890',
                    'is_connected'             => true,
                    'is_enabled'               => true,
                    'auto_fallback_to_central' => true,
                    'gateway_status'           => 'ready',
                ],
            ], 200),
        ]);

        $status = WhatsAppBot::status();

        $this->assertTrue($status['success']);
        $this->assertTrue($status['data']['whatsapp_gateway']['is_connected']);
        $this->assertEquals('6281234567890', $status['data']['whatsapp_gateway']['assigned_phone']);
        $this->assertTrue(WhatsAppBot::isConnected());
    }

    public function test_send_baileys_whatsapp_job_updates_log_when_sent_via_sipintu(): void
    {
        Http::fake([
            '*/api/v1/whatsapp/send' => Http::response([
                'status'  => 'queued',
                'message' => 'Pesan WhatsApp berhasil dimasukkan ke dalam antrean pengiriman.',
            ], 202),
        ]);

        $log = WhatsappLog::create([
            'user_id'        => $this->admin->id,
            'recipient_name' => 'Bapak Guru',
            'phone'          => '628123456789',
            'context'        => 'pengingat_piket',
            'message'        => 'Jadwal piket Anda aktif hari ini.',
            'status'         => 'pending',
        ]);

        (new SendBaileysWhatsAppJob($log->id))->handle();

        $this->assertEquals('sent', $log->fresh()->status);
        $this->assertNotNull($log->fresh()->sent_at);
        $this->assertNull($log->fresh()->error_message);
    }
}
