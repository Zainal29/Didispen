<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWhatsappPiketTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Guru $guru;
    private Guru $koordinator;
    private JadwalPiket $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user admin
        $this->admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@didispen.test',
            'role' => 'admin',
            'nis_nip' => 'ADMIN001',
            'password' => Hash::make('password'),
        ]);

        // Buat User Koordinator & Guru
        $userKoor = User::create([
            'name' => "Almu'idul 'Afwan, S.Pd.",
            'email' => 'koor@didispen.test',
            'role' => 'guru',
            'nis_nip' => '198501012010011001',
            'password' => Hash::make('password'),
        ]);

        $this->koordinator = Guru::create([
            'user_id' => $userKoor->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => "Almu'idul 'Afwan, S.Pd.",
            'no_telepon' => '085741322231',
            'status_aktif' => true,
        ]);

        $userGuru = User::create([
            'name' => 'Akhmad Lutfianto',
            'email' => 'akhmad@didispen.test',
            'role' => 'guru',
            'nis_nip' => '199002022015021002',
            'password' => Hash::make('password'),
        ]);

        $this->guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '199002022015021002',
            'nama_lengkap' => 'Akhmad Lutfianto',
            'no_telepon' => '082327046669',
            'status_aktif' => true,
        ]);

        // Buat Jadwal Piket
        $this->jadwal = JadwalPiket::create([
            'hari' => 1, // Senin
            'nama_sesi' => 'Sesi 1',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'koordinator_guru_id' => $this->koordinator->id,
            'tanggal_mulai_berlaku' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->jadwal->guru()->attach($this->guru->id);
    }

    public function test_whatsapp_service_generates_correct_piket_wa_me_link(): void
    {
        $waService = app(WhatsappMessageService::class);
        $link = $waService->generatePiketWaLink($this->guru, $this->jadwal);

        $this->assertNotNull($link);
        $this->assertStringStartsWith('https://wa.me/6282327046669?text=', $link);

        // Decode query param
        parse_str(parse_url($link, PHP_URL_QUERY), $queryParams);
        $text = $queryParams['text'] ?? '';

        $this->assertStringContainsString('Akhmad Lutfianto', $text);
        $this->assertStringContainsString('Senin', $text);
        $this->assertStringContainsString('Sesi 1', $text);
        $this->assertStringContainsString('07:00', $text);
        $this->assertStringContainsString('09:30', $text);
        $this->assertStringContainsString("Almu'idul 'Afwan, S.Pd.", $text);
    }

    public function test_whatsapp_service_generates_correct_koordinator_wa_me_link(): void
    {
        $waService = app(WhatsappMessageService::class);
        $link = $waService->generatePiketWaLink($this->koordinator, $this->jadwal);

        $this->assertNotNull($link);
        $this->assertStringStartsWith('https://wa.me/6285741322231?text=', $link);

        parse_str(parse_url($link, PHP_URL_QUERY), $queryParams);
        $text = $queryParams['text'] ?? '';
        $this->assertStringContainsString("Almu'idul 'Afwan, S.Pd.", $text);
    }

    public function test_admin_can_view_whatsapp_templates_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.whatsapp-templates.index'));

        $response->assertOk();
        $response->assertSee('Template Pesan WhatsApp');
        $response->assertSee('{nama_guru}');
        $response->assertSee('{hari}');
        $response->assertSee('{nama_sesi}');
    }

    public function test_admin_can_preview_piket_whatsapp_template(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.whatsapp-templates.preview'), [
            'content' => 'Halo {nama_guru}, Anda piket hari {hari} pada {nama_sesi} ({jam_mulai} - {jam_selesai}) koordinator {koordinator}.',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['preview']);
        $previewText = $response->json('preview');
        $this->assertStringContainsString('Budi Santoso, S.Pd.', $previewText);
        $this->assertStringContainsString('Senin', $previewText);
        $this->assertStringContainsString('Sesi 1', $previewText);
    }

    public function test_admin_can_create_and_update_piket_whatsapp_template(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.whatsapp-templates.store'), [
            'name' => 'Pengingat Piket Kustom',
            'content' => 'Pesan pengingat untuk {nama_guru} pada hari {hari}.',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.whatsapp-templates.index'));
        $this->assertDatabaseHas('whatsapp_templates', [
            'name' => 'Pengingat Piket Kustom',
            'slug' => 'pengingat-piket-kustom',
        ]);

        $template = WhatsappTemplate::where('slug', 'pengingat-piket-kustom')->first();

        // Update template
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.whatsapp-templates.update', $template), [
            'name' => 'Pengingat Piket Kustom Baru',
            'content' => 'Versi baru pesan untuk {nama_guru}.',
            'is_active' => true,
        ]);

        $responseUpdate->assertRedirect(route('admin.whatsapp-templates.index'));
        $this->assertDatabaseHas('whatsapp_templates', [
            'name' => 'Pengingat Piket Kustom Baru',
            'slug' => 'pengingat-piket-kustom-baru',
        ]);
    }

    public function test_jadwal_piket_matrix_view_contains_clickable_wa_me_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.jadwal-piket.index', ['view' => 'matriks']));

        $response->assertOk();
        // Check for wa.me links in the page
        $response->assertSee('https://wa.me/6282327046669', false);
        $response->assertSee('https://wa.me/6285741322231', false);
    }

    public function test_jadwal_piket_list_view_contains_clickable_wa_me_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.jadwal-piket.index'));

        $response->assertOk();
        $response->assertSee('https://wa.me/6282327046669', false);
        $response->assertSee('https://wa.me/6285741322231', false);
    }

    public function test_jadwal_piket_show_view_contains_clickable_wa_me_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.jadwal-piket.show', $this->jadwal));

        $response->assertOk();
        $response->assertSee('https://wa.me/6282327046669', false);
        $response->assertSee('https://wa.me/6285741322231', false);
    }
}
