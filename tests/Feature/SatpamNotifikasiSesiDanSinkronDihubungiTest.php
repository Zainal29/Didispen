<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WhatsappLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SatpamNotifikasiSesiDanSinkronDihubungiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-22 08:30:00', 'Asia/Jakarta'));

        Http::fake([
            '*' => Http::response(['success' => true, 'status' => 'sent'], 200),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_notifikasi_siaga_sesi_piket_terkirim_ke_satpam_dan_tidak_duplikat()
    {
        Setting::set('wa_bot_enabled', '1');
        Setting::set('wa_notify_satpam_sesi_piket', '1');
        Setting::set('wa_satpam_phone', '081234567890');

        $guruUser = User::create([
            'name' => 'Guru Piket Pagi',
            'email' => 'guru@smkn1bangsri.sch.id',
            'role' => 'guru',
            'password' => Hash::make('secret123'),
            'password_changed_at' => now(),
            'no_wa' => '081111111111',
        ]);

        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Guru Piket Pagi',
            'nip' => '198501012010012001',
            'no_telepon' => '081111111111',
            'status_aktif' => true,
        ]);

        $koorUser = User::create([
            'name' => 'Koordinator Piket',
            'email' => 'koor@smkn1bangsri.sch.id',
            'role' => 'guru',
            'password' => Hash::make('secret123'),
            'password_changed_at' => now(),
            'no_wa' => '082222222222',
        ]);

        $koordinator = Guru::create([
            'user_id' => $koorUser->id,
            'nama_lengkap' => 'Koordinator Piket',
            'nip' => '198001012005011001',
            'no_telepon' => '082222222222',
            'status_aktif' => true,
        ]);

        $jadwal = JadwalPiket::create([
            'hari' => 4, // Kamis (22 Okt 2026)
            'nama_sesi' => 'Sesi Pagi',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '10:00:00',
            'is_active' => true,
            'tanggal_mulai_berlaku' => '2026-01-01',
            'koordinator_guru_id' => $koordinator->id,
        ]);

        JadwalPiketGuru::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guru->id,
        ]);

        // Jalankan command monitor
        $this->artisan('didispen:monitor-wa')->assertSuccessful();

        // Verifikasi log notifikasi ke satpam
        $satpamLogs = WhatsappLog::where('phone', 'like', '%81234567890%')
            ->where('context', 'satpam_sesi_piket')
            ->get();

        $this->assertCount(1, $satpamLogs);
        $log = $satpamLogs->first();
        $this->assertStringContainsString('PENGINGAT SIAGA POS GERBANG', $log->message);
        $this->assertStringContainsString('Sesi Pagi', $log->message);
        $this->assertStringContainsString('Guru Piket Pagi', $log->message);

        // Jalankan lagi command monitor: harus deduplikasi (tidak kirim 2x)
        $this->artisan('didispen:monitor-wa')->assertSuccessful();
        $this->assertCount(1, WhatsappLog::where('phone', 'like', '%81234567890%')->get());
    }

    public function test_siswa_terlambat_otomatis_tercatat_warned_by_bot_pada_cron()
    {
        Setting::set('wa_bot_enabled', '1');
        Setting::set('wa_notify_siswa_terlambat', '1');

        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 1', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);
        $userSiswa = User::create([
            'name' => 'Ahmad Siswa',
            'email' => 'ahmad@smkn1bangsri.sch.id',
            'role' => 'siswa',
            'password' => Hash::make('secret123'),
            'password_changed_at' => now(),
            'no_wa' => '089999999999',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nama_lengkap' => 'Ahmad Siswa',
            'nis_nip' => '4001',
            'no_telepon' => '089999999999',
            'status_aktif' => true,
        ]);

        $dispensasi = Dispensasi::create([
            'siswa_id' => $siswa->id,
            'nomor_surat' => 'DISP/20261022/TEST01',
            'tipe_pengajuan' => 'individu',
            'kategori' => 'keluar_sekolah',
            'alasan' => 'Urusan Bank',
            'tujuan' => 'Bank Mandiri',
            'tanggal_izin' => '2026-10-22',
            'jam_keluar' => '07:00:00',
            'jam_kembali' => '08:00:00',
            'sampai_pulang' => false,
            'batas_waktu_kembali' => Carbon::parse('2026-10-22 08:00:00'),
            'status' => 'keluar',
            'is_warned' => false,
        ]);

        $this->assertTrue($dispensasi->isOverdue());

        // Jalankan monitor cron
        $this->artisan('didispen:monitor-wa')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertTrue($dispensasi->is_warned);
        $this->assertEquals('bot', $dispensasi->warned_by);
        $this->assertStringContainsString('Sudah dihubungi bot WA DIDISPEN', $dispensasi->getWarnedText());
    }

    public function test_hubungi_manual_oleh_satpam_mencatat_warned_by_satpam()
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 2', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        $userSiswa = User::create([
            'name' => 'Budi Siswa',
            'email' => 'budi@smkn1bangsri.sch.id',
            'role' => 'siswa',
            'password' => Hash::make('secret123'),
            'password_changed_at' => now(),
            'no_wa' => '089999999998',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nama_lengkap' => 'Budi Siswa',
            'nis_nip' => '4002',
            'status_aktif' => true,
        ]);

        $satpamUser = User::create([
            'name' => 'Satpam Pos',
            'email' => 'satpam@smkn1bangsri.sch.id',
            'role' => 'satpam',
            'password' => Hash::make('secret123'),
        ]);

        $disp1 = Dispensasi::create([
            'siswa_id' => $siswa->id,
            'nomor_surat' => 'DISP/20261022/MAN01',
            'tipe_pengajuan' => 'individu',
            'kategori' => 'keluar_sekolah',
            'alasan' => 'Lomba',
            'tujuan' => 'GOR',
            'tanggal_izin' => '2026-10-22',
            'jam_keluar' => '07:00:00',
            'jam_kembali' => '08:00:00',
            'status' => 'keluar',
        ]);

        $this->actingAs($satpamUser)
            ->post(route('satpam.wa-contacted', $disp1->id))
            ->assertOk();

        $disp1->refresh();
        $this->assertTrue($disp1->is_warned);
        $this->assertEquals('satpam', $disp1->warned_by);
        $this->assertStringContainsString('Sudah Dihubungi Satpam', $disp1->getWarnedText());
    }

    public function test_hubungi_manual_oleh_guru_mencatat_warned_by_guru()
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 3', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        $userSiswa = User::create([
            'name' => 'Citra Siswa',
            'email' => 'citra@smkn1bangsri.sch.id',
            'role' => 'siswa',
            'password' => Hash::make('secret123'),
            'password_changed_at' => now(),
            'no_wa' => '089999999997',
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'kelas_id' => $kelas->id,
            'nama_lengkap' => 'Citra Siswa',
            'nis_nip' => '4003',
            'status_aktif' => true,
        ]);

        $guruUser = User::create([
            'name' => 'Guru Piket Dua',
            'email' => 'gurupiket2@smkn1bangsri.sch.id',
            'role' => 'guru',
            'password' => Hash::make('secret123'),
            'password_changed_at' => now(),
            'no_wa' => '083333333333',
        ]);

        Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Guru Piket Dua',
            'nip' => '198501012010012010',
            'no_telepon' => '083333333333',
            'status_aktif' => true,
        ]);

        $disp = Dispensasi::create([
            'siswa_id' => $siswa->id,
            'nomor_surat' => 'DISP/20261022/MAN03',
            'tipe_pengajuan' => 'individu',
            'kategori' => 'keluar_sekolah',
            'alasan' => 'Lomba',
            'tujuan' => 'GOR',
            'tanggal_izin' => '2026-10-22',
            'jam_keluar' => '07:00:00',
            'jam_kembali' => '08:00:00',
            'status' => 'keluar',
        ]);

        $this->actingAs($guruUser)
            ->postJson("/guru/dispensasi/{$disp->id}/wa-contacted")
            ->assertOk();

        $disp->refresh();
        $this->assertTrue($disp->is_warned);
        $this->assertEquals('guru', $disp->warned_by);
        $this->assertStringContainsString('Sudah Dihubungi Guru Piket', $disp->getWarnedText());
    }

    public function test_satpam_dapat_melihat_dan_memperbarui_nomor_wa_di_halaman_profil()
    {
        $satpamUser = User::create([
            'name' => 'Petugas Satpam',
            'email' => 'satpam_test@smkn1bangsri.sch.id',
            'role' => 'satpam',
            'password' => Hash::make('satpam123'),
            'password_changed_at' => now(),
            'nis_nip' => 'SATPAM001',
            'no_wa' => '+6289501963463',
        ]);

        $response = $this->actingAs($satpamUser)->get('/profil');
        $response->assertOk();
        $response->assertSee('089501963463');
        $response->assertSee('Kontak WhatsApp Satpam');

        // Test update nomor WA oleh Satpam
        $updateResponse = $this->actingAs($satpamUser)->post('/profil/update-additional', [
            'no_wa' => '089599998888',
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $satpamUser->refresh();
        $this->assertEquals('+6289599998888', $satpamUser->no_wa);
        $this->assertEquals('6289599998888', Setting::get('wa_satpam_phone'));
    }

    public function test_template_pengingat_satpam_tersedia_di_database_dan_dipakai_notifikasi()
    {
        $tpl = \App\Models\WhatsappTemplate::where('slug', 'pengingat-satpam')->first();
        $this->assertNotNull($tpl, 'Template pengingat-satpam harus ada di tabel whatsapp_templates');
        $this->assertEquals('Pengingat Satpam Standby Gerbang', $tpl->name);
        $this->assertStringContainsString('PENGINGAT SIAGA POS GERBANG', $tpl->content);
        $this->assertTrue((bool) $tpl->is_active);
    }
}
