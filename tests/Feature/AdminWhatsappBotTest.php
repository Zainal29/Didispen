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
            'wa_notify_koordinator_piket'  => '1',
            'wa_notify_siswa_terlambat'    => '1',
            'wa_notify_laporan_guru_piket' => '1',
            'wa_active_days'               => ['1', '2', '3', '4', '5'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('http://127.0.0.1:3789', Setting::get('wa_bot_url'));
        $this->assertEquals('new_secret_key_2026', Setting::get('wa_bot_api_key'));
        $this->assertTrue((bool) Setting::get('wa_bot_enabled'));
        $this->assertTrue((bool) Setting::get('wa_notify_piket_bertugas'));
        $this->assertTrue((bool) Setting::get('wa_notify_koordinator_piket'));
        $this->assertTrue((bool) Setting::get('wa_notify_siswa_terlambat'));
        $this->assertTrue((bool) Setting::get('wa_notify_laporan_guru_piket'));
        $this->assertEquals([1, 2, 3, 4, 5], json_decode(Setting::get('wa_active_days'), true));
    }

    public function test_admin_can_uncheck_all_active_days_and_notifications(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.settings'), [
            'wa_bot_url'     => 'http://127.0.0.1:3789',
            'wa_bot_api_key' => 'new_secret_key_2026',
            // Tanpa wa_bot_enabled, wa_notify_piket_bertugas, wa_active_days (semua diuncheck)
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse(filter_var(Setting::get('wa_bot_enabled'), FILTER_VALIDATE_BOOLEAN));
        $this->assertFalse(filter_var(Setting::get('wa_notify_piket_bertugas'), FILTER_VALIDATE_BOOLEAN));
        $this->assertFalse(filter_var(Setting::get('wa_notify_koordinator_piket'), FILTER_VALIDATE_BOOLEAN));
        $this->assertEquals([], json_decode(Setting::get('wa_active_days'), true));

        // Pastikan saat load halaman index, nilainya tetap false / kosong
        Http::fake([
            '*/status' => Http::response(['online' => true, 'connected' => false], 200),
        ]);

        $viewResponse = $this->actingAs($this->admin)->get(route('admin.whatsapp-bot.index'));
        $viewResponse->assertOk();
        $settings = $viewResponse->viewData('settings');
        $this->assertFalse($settings['wa_notify_piket_bertugas']);
        $this->assertEquals([], $settings['wa_active_days']);
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

    public function test_admin_can_toggle_master_whatsapp_bot(): void
    {
        Setting::set('wa_bot_enabled', true);

        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.toggle'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse((bool) Setting::get('wa_bot_enabled'));

        // Toggle kembali ke true
        $response2 = $this->actingAs($this->admin)->post(route('admin.whatsapp-bot.toggle'));
        $response2->assertRedirect();
        $this->assertTrue((bool) Setting::get('wa_bot_enabled'));
    }

    public function test_monitor_wa_sends_to_all_piket_teachers_in_session_and_proceeds_to_next_session(): void
    {
        Http::fake([
            '*/send-message' => Http::response(['status' => 'success'], 200),
        ]);

        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-10-19 07:05:00', 'Asia/Jakarta')); // Senin, jam 07:05

        // Buat 3 Guru untuk Sesi 1
        $guru1User = User::create(['name' => 'Guru 1', 'email' => 'g1@test.com', 'password' => bcrypt('123'), 'role' => 'guru', 'nis_nip' => 'G01']);
        $guru1 = \App\Models\Guru::create(['user_id' => $guru1User->id, 'nip' => 'G01', 'nama_lengkap' => 'Guru Satu', 'no_telepon' => '081211111111', 'status_aktif' => true]);

        $guru2User = User::create(['name' => 'Guru 2', 'email' => 'g2@test.com', 'password' => bcrypt('123'), 'role' => 'guru', 'nis_nip' => 'G02']);
        $guru2 = \App\Models\Guru::create(['user_id' => $guru2User->id, 'nip' => 'G02', 'nama_lengkap' => 'Guru Dua', 'no_telepon' => '081222222222', 'status_aktif' => true]);

        $guru3User = User::create(['name' => 'Guru 3', 'email' => 'g3@test.com', 'password' => bcrypt('123'), 'role' => 'guru', 'nis_nip' => 'G03']);
        $guru3 = \App\Models\Guru::create(['user_id' => $guru3User->id, 'nip' => 'G03', 'nama_lengkap' => 'Guru Tiga', 'no_telepon' => '081233333333', 'status_aktif' => true]);

        // Buat 2 Guru untuk Sesi 2
        $guru4User = User::create(['name' => 'Guru 4', 'email' => 'g4@test.com', 'password' => bcrypt('123'), 'role' => 'guru', 'nis_nip' => 'G04']);
        $guru4 = \App\Models\Guru::create(['user_id' => $guru4User->id, 'nip' => 'G04', 'nama_lengkap' => 'Guru Empat', 'no_telepon' => '081244444444', 'status_aktif' => true]);

        $guru5User = User::create(['name' => 'Guru 5', 'email' => 'g5@test.com', 'password' => bcrypt('123'), 'role' => 'guru', 'nis_nip' => 'G05']);
        $guru5 = \App\Models\Guru::create(['user_id' => $guru5User->id, 'nip' => 'G05', 'nama_lengkap' => 'Guru Lima', 'no_telepon' => '081255555555', 'status_aktif' => true]);

        // Buat Koordinator Piket
        $koorUser = User::create(['name' => 'Koordinator', 'email' => 'koor@test.com', 'password' => bcrypt('123'), 'role' => 'guru', 'nis_nip' => 'K01']);
        $koor = \App\Models\Guru::create(['user_id' => $koorUser->id, 'nip' => 'K01', 'nama_lengkap' => 'Pak Koordinator', 'no_telepon' => '081299999999', 'status_aktif' => true]);

        // Sesi 1: Senin 07:00 - 09:30 (3 Guru + 1 Koordinator)
        $sesi1 = \App\Models\JadwalPiket::create([
            'hari' => 1, // Senin
            'nama_sesi' => 'Sesi 1 (Pagi)',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'koordinator_guru_id' => $koor->id,
            'tanggal_mulai_berlaku' => '2026-01-01',
            'is_active' => true,
        ]);
        $sesi1->guru()->attach([$guru1->id, $guru2->id, $guru3->id]);

        // Sesi 2: Senin 09:30 - 12:00 (2 Guru)
        $sesi2 = \App\Models\JadwalPiket::create([
            'hari' => 1, // Senin
            'nama_sesi' => 'Sesi 2 (Siang)',
            'jam_mulai' => '09:30:00',
            'jam_selesai' => '12:00:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'is_active' => true,
        ]);
        $sesi2->guru()->attach([$guru4->id, $guru5->id]);

        // JALANKAN COMMAND PERTAMA KALI DI SESI 1 (07:05)
        $this->artisan('didispen:monitor-wa')->assertExitCode(0);

        // 1. Pastikan ketiga guru di Sesi 1 menerima notifikasi WA piket_aktif
        $this->assertDatabaseHas('whatsapp_logs', ['phone' => '6281211111111', 'context' => 'piket_aktif']);
        $this->assertDatabaseHas('whatsapp_logs', ['phone' => '6281222222222', 'context' => 'piket_aktif']);
        $this->assertDatabaseHas('whatsapp_logs', ['phone' => '6281233333333', 'context' => 'piket_aktif']);
        $this->assertEquals(3, WhatsappLog::where('context', 'piket_aktif')->count());

        // 2. Pastikan Koordinator juga menerima notifikasi khusus piket_koordinator
        $this->assertDatabaseHas('whatsapp_logs', [
            'phone' => '6281299999999',
            'context' => 'piket_koordinator',
        ]);
        $koorLog = WhatsappLog::where('context', 'piket_koordinator')->first();
        $this->assertNotNull($koorLog);
        $this->assertStringContainsString('Pak Koordinator', $koorLog->message);
        $this->assertStringContainsString('Guru Satu', $koorLog->message);

        // 3. JALANKAN LAGI 5 MENIT KEMUDIAN DI SESI 1 (07:10) -> TIDAK BOLEH TERKIRIM ULANG / SPAM
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-10-19 07:10:00', 'Asia/Jakarta'));
        $this->artisan('didispen:monitor-wa')->assertExitCode(0);
        $this->assertEquals(3, WhatsappLog::where('context', 'piket_aktif')->count()); // Tetap 3!
        $this->assertEquals(1, WhatsappLog::where('context', 'piket_koordinator')->count()); // Tetap 1!

        // 4. MAJUKAN WAKTU KE SESI 2 (09:35) -> GURU SESI 2 HARUS MENERIMA NOTIFIKASI
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-10-19 09:35:00', 'Asia/Jakarta'));
        $this->artisan('didispen:monitor-wa')->assertExitCode(0);

        // Pastikan kedua guru di Sesi 2 sekarang juga menerima notifikasi WA
        $this->assertDatabaseHas('whatsapp_logs', ['phone' => '6281244444444', 'context' => 'piket_aktif']);
        $this->assertDatabaseHas('whatsapp_logs', ['phone' => '6281255555555', 'context' => 'piket_aktif']);
        $this->assertEquals(5, WhatsappLog::where('context', 'piket_aktif')->count());

        \Carbon\Carbon::setTestNow(); // Reset waktu
    }
}
