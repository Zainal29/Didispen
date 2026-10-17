<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminWhatsappBotTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin.test@didispen.sch.id',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'nis_nip'  => 'ADM999',
        ]);

        $this->siswa = User::create([
            'name'     => 'Siswa Test',
            'email'    => 'siswa.test@didispen.sch.id',
            'password' => bcrypt('password'),
            'role'     => 'siswa',
            'nis_nip'  => '9999',
        ]);
    }

    public function test_admin_can_view_whatsapp_bot_page(): void
    {
        Http::fake([
            '*/status' => Http::response([
                'online'    => true,
                'connected' => false,
                'status'    => 'waiting_qr',
                'user'      => null,
                'qr'        => 'dummy_qr_code',
                'qr_image'  => 'data:image/png;base64,dummy',
                'message'   => 'Menunggu Scan QR Code',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.whatsapp-bot.index'));
        $response->assertOk();
        $response->assertSee('WhatsApp Bot & Notifikasi Otomatis', false);
        $response->assertSee('Koneksi Perangkat WhatsApp (QR Code Scanner)');
        $response->assertSee('Menunggu Scan QR');
    }

    public function test_non_admin_cannot_view_whatsapp_bot_page(): void
    {
        $response = $this->actingAs($this->siswa)->get(route('admin.whatsapp-bot.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_poll_status_ajax(): void
    {
        Http::fake([
            '*/status' => Http::response([
                'online'    => true,
                'connected' => true,
                'status'    => 'connected',
                'user'      => ['phone' => '628123456789', 'name' => 'Bot SMKN 1 Bangsri'],
                'qr'        => null,
                'qr_image'  => null,
                'message'   => 'Bot Terhubung ke WhatsApp',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.whatsapp-bot.status'));
        $response->assertOk();
        $response->assertJson([
            'online'    => true,
            'connected' => true,
            'user'      => [
                'phone' => '628123456789',
            ],
        ]);
    }

    public function test_admin_can_update_whatsapp_bot_settings(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.settings'), [
            'wa_bot_url'                   => 'http://127.0.0.1:3789',
            'wa_bot_api_key'               => 'new_secret_key_2026',
            'wa_bot_enabled'               => '1',
            'wa_notify_piket_bertugas'     => '1',
            'wa_notify_siswa_terlambat'    => '1',
            'wa_notify_laporan_guru_piket' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('http://127.0.0.1:3789', Setting::get('wa_bot_url'));
        $this->assertEquals('new_secret_key_2026', Setting::get('wa_bot_api_key'));
        $this->assertTrue((bool) Setting::get('wa_bot_enabled'));
        $this->assertTrue((bool) Setting::get('wa_notify_piket_bertugas'));
        $this->assertTrue((bool) Setting::get('wa_notify_siswa_terlambat'));
        $this->assertTrue((bool) Setting::get('wa_notify_laporan_guru_piket'));
    }

    public function test_admin_can_send_test_message(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.send-test'), [
            'phone'   => '081234567890',
            'message' => 'Test pesan dari unit test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('whatsapp_logs', [
            'phone'   => '6281234567890',
            'context' => 'test_admin',
            'message' => 'Test pesan dari unit test',
            'status'  => 'pending',
        ]);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendBaileysWhatsAppJob::class);
    }

    public function test_admin_can_retry_failed_whatsapp_log(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $log = WhatsappLog::create([
            'user_id'        => $this->admin->id,
            'recipient_name' => 'Test Recipient',
            'phone'          => '6281234567890',
            'context'        => 'test_admin',
            'message'        => 'Halo gagal',
            'status'         => 'failed',
            'error_message'  => 'Timeout error',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.retry', $log));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('pending', $log->fresh()->status);
        $this->assertNull($log->fresh()->error_message);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendBaileysWhatsAppJob::class);
    }

    public function test_admin_can_disconnect_session(): void
    {
        Http::fake([
            '*/logout' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.disconnect'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_console_monitor_command_executes_successfully(): void
    {
        $this->artisan('didispen:monitor-wa')->assertExitCode(0);
    }
}
