<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DispensasiPaket4SatpamExpressDanAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $userAdmin;
    private User $userSatpam;
    private User $userGuru;
    private User $userSiswa;
    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(9, 0, 0));

        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 1', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        $this->userAdmin = User::create([
            'name'     => 'Admin Utama',
            'email'    => 'admin@smkn1bangsri.sch.id',
            'role'     => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $this->userSatpam = User::create([
            'name'     => 'Bapak Satpam Pos',
            'email'    => 'satpam@smkn1bangsri.sch.id',
            'role'     => 'satpam',
            'password' => Hash::make('password123'),
        ]);

        $this->userGuru = User::create([
            'name'     => 'Ibu Guru Piket',
            'email'    => 'guru@smkn1bangsri.sch.id',
            'role'     => 'guru',
            'password' => Hash::make('password123'),
        ]);

        Guru::create([
            'user_id'      => $this->userGuru->id,
            'nama_lengkap' => 'Ibu Guru Piket',
            'nip'          => '198501012010012001',
            'status_aktif' => true,
        ]);

        $this->userSiswa = User::create([
            'name'     => 'Rian Pratama',
            'email'    => 'rian@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password123'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->userSiswa->id,
            'nama_lengkap' => 'Rian Pratama',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
            'status_aktif' => true,
        ]);
    }

    /**
     * 1. Admin melihat pengaturan Mode Pos Gerbang & Autentikasi di settings page
     */
    public function test_admin_melihat_pengaturan_satpam_express_dan_auth(): void
    {
        $response = $this->actingAs($this->userAdmin)->get(route('admin.settings.index'));
        $response->assertOk();
        $response->assertSee('Mode Pos Gerbang (Satpam Express)');
        $response->assertSee('satpam_auto_remember');
        $response->assertSee('guru_auto_remember');
        $response->assertSee('satpam_express_mode');
        $response->assertSee('satpam_beep_sound');
        $response->assertSee('satpam_wake_lock');
    }

    /**
     * 2. Auto-Remember Login Satpam aktif & Redirect langsung ke Scanner
     */
    public function test_login_satpam_otomatis_remember_dan_redirect_ke_scanner(): void
    {
        Setting::set('satpam_auto_remember', '1');

        $response = $this->post(route('login.post'), [
            'email'    => 'satpam@smkn1bangsri.sch.id',
            'password' => 'password123',
            'role'     => 'satpam',
        ]);

        $response->assertRedirect(route('satpam.scan'));
        $this->assertAuthenticatedAs($this->userSatpam);

        // Pastikan cookie remember me aktif disetel
        $this->assertNotNull(Auth::user()->getRememberToken());
    }

    /**
     * 3. Auto-Remember Login Guru aktif
     */
    public function test_login_guru_otomatis_remember(): void
    {
        Setting::set('guru_auto_remember', '1');

        $response = $this->post(route('login.post'), [
            'email'    => 'guru@smkn1bangsri.sch.id',
            'password' => 'password123',
            'role'     => 'guru',
        ]);

        $response->assertRedirect(url('/guru/dashboard'));
        $this->assertAuthenticatedAs($this->userGuru);
        $this->assertNotNull(Auth::user()->getRememberToken());
    }

    /**
     * 4. Halaman scanner satpam memuat panel kontrol express & wake lock
     */
    public function test_halaman_scanner_satpam_memuat_panel_kontrol(): void
    {
        Setting::set('satpam_express_mode', '1');
        Setting::set('satpam_beep_sound', '1');
        Setting::set('satpam_wake_lock', '1');

        $response = $this->actingAs($this->userSatpam)->get(route('satpam.scan'));
        $response->assertOk();
        $response->assertSee('Kontrol Operasional Pos');
        $response->assertSee('btnToggleExpress');
        $response->assertSee('btnToggleBeep');
        $response->assertSee('btnToggleWakeLock');
        $response->assertSee('expressOverlay');
        $response->assertSee('AudioHelper');
        $response->assertSee('requestWakeLock');
    }

    /**
     * 5. Mode Satpam Express: auto-confirm keluar dan kembali berjalan sukses
     */
    public function test_satpam_express_auto_confirm_keluar_dan_kembali(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/EXP/001',
            'kategori'             => 'izin',
            'alasan'               => 'Keperluan keluarga',
            'tujuan'               => 'Rumah',
            'tipe_pengajuan'       => 'individu',
            'tanggal_pengajuan'    => Carbon::today(),
            'jam_keluar'           => 1,
            'jam_kembali'          => 10,
            'qr_token'             => \Illuminate\Support\Str::random(32),
            'status'               => 'disetujui',
            'status_persetujuan'   => 'disetujui',
            'approved_at'          => Carbon::now(),
        ]);

        // A. Auto-confirm KELUAR
        $responseKeluar = $this->actingAs($this->userSatpam)->postJson(route('satpam.scan.verify'), [
            'qr_data' => $dispensasi->qr_token,
            'action'  => 'auto',
        ]);

        $responseKeluar->assertOk();
        $responseKeluar->assertJson([
            'success' => true,
            'action'  => 'keluar',
        ]);

        $dispensasi->refresh();
        $this->assertEquals('keluar', $dispensasi->status);
        $this->assertNotNull($dispensasi->waktu_keluar_aktual);

        // B. Auto-confirm KEMBALI
        $responseKembali = $this->actingAs($this->userSatpam)->postJson(route('satpam.scan.verify'), [
            'qr_data' => $dispensasi->qr_token,
            'action'  => 'auto',
        ]);

        $responseKembali->assertOk();
        $responseKembali->assertJson([
            'success' => true,
        ]);

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertNotNull($dispensasi->waktu_kembali_aktual);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
