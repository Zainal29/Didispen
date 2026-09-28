<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\PertukaranJadwalPiket;
use App\Models\Siswa;
use App\Models\User;
use App\Services\GuruPiketService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminJadwalPiketTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin Sistem',
            'email' => 'admin_' . rand(1000, 9999) . '@sch.id',
            'role' => 'admin',
            'nis_nip' => 'ADM' . rand(100, 999),
            'password' => Hash::make('password'),
        ]);
    }

    private function createGuruUser(string $name = 'Guru Test', bool $active = true): array
    {
        $unique = rand(100000, 999999);
        $user = User::create([
            'name' => $name,
            'email' => "guru_{$unique}@sch.id",
            'role' => 'guru',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => (string) $unique,
            'nama_lengkap' => $name,
            'status_aktif' => $active,
        ]);

        return [$user, $guru];
    }

    private function createSiswaUser(): User
    {
        $unique = rand(100000, 999999);
        $user = User::create([
            'name' => 'Siswa Test',
            'email' => "siswa_{$unique}@sch.id",
            'role' => 'siswa',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        Siswa::create([
            'user_id' => $user->id,
            'nis' => (string) $unique,
            'nama_lengkap' => 'Siswa Test',
            'status_aktif' => true,
        ]);

        return $user;
    }

    private function createSatpamUser(): User
    {
        $unique = rand(1000, 9999);
        return User::create([
            'name' => 'Satpam Test',
            'email' => "satpam_{$unique}@sch.id",
            'role' => 'satpam',
            'nis_nip' => "SAT{$unique}",
            'password' => Hash::make('password'),
        ]);
    }

    private function createJadwal(int $hari = 1, string $jamMulai = '07:00:00', string $jamSelesai = '09:30:00', bool $active = true): JadwalPiket
    {
        return JadwalPiket::create([
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'tanggal_mulai_berlaku' => '2026-01-01',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'is_active' => $active,
        ]);
    }

    /**
     * Test 1: Admin dapat melihat daftar jadwal.
     */
    public function test_1_admin_dapat_melihat_daftar_jadwal(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Piket 1');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        $jadwal->guru()->sync([$guru->id]);

        $response = $this->actingAs($admin)->get(route('admin.jadwal-piket.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Jadwal Guru Piket');
        $response->assertSee('Guru Piket 1');
        $response->assertSee('07:00 - 09:30');
    }

    /**
     * Test 2: Admin dapat membuka form create.
     */
    public function test_2_admin_dapat_membuka_form_create(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Kandidat');

        $response = $this->actingAs($admin)->get(route('admin.jadwal-piket.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Jadwal Piket Baru');
        $response->assertSee('Guru Kandidat');
    }

    /**
     * Test 3: Admin dapat membuat jadwal.
     */
    public function test_3_admin_dapat_membuat_jadwal(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Baru');

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 2, // Selasa
            'jam_mulai' => '07:30',
            'jam_selesai' => '10:00',
            'tanggal_mulai_berlaku' => '2026-07-01',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$guru->id],
        ]);

        $response->assertRedirect(route('admin.jadwal-piket.index'));
        $this->assertDatabaseHas('jadwal_piket', [
            'hari' => 2,
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '10:00:00',
            'is_active' => true,
        ]);

        $jadwal = JadwalPiket::where('hari', 2)->first();
        $this->assertDatabaseHas('jadwal_piket_guru', [
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guru->id,
        ]);
    }

    /**
     * Test 4: Guru tidak dapat membuka halaman Admin jadwal.
     */
    public function test_4_guru_tidak_dapat_membuka_halaman_admin_jadwal(): void
    {
        [$guruUser, $guru] = $this->createGuruUser('Guru User');

        $response = $this->actingAs($guruUser)->get(route('admin.jadwal-piket.index'));
        $response->assertStatus(403);

        $responsePost = $this->actingAs($guruUser)->post(route('admin.jadwal-piket.store'), []);
        $responsePost->assertStatus(403);
    }

    /**
     * Test 5: Siswa tidak dapat membuka halaman Admin jadwal.
     */
    public function test_5_siswa_tidak_dapat_membuka_halaman_admin_jadwal(): void
    {
        $siswa = $this->createSiswaUser();

        $response = $this->actingAs($siswa)->get(route('admin.jadwal-piket.index'));
        $response->assertStatus(403);

        $responseCreate = $this->actingAs($siswa)->get(route('admin.jadwal-piket.create'));
        $responseCreate->assertStatus(403);
    }

    /**
     * Test 6: Satpam tidak dapat membuka halaman Admin jadwal.
     */
    public function test_6_satpam_tidak_dapat_membuka_halaman_admin_jadwal(): void
    {
        $satpam = $this->createSatpamUser();

        $response = $this->actingAs($satpam)->get(route('admin.jadwal-piket.index'));
        $response->assertStatus(403);
    }

    /**
     * Test 7: Guru nonaktif tidak dapat ditugaskan.
     */
    public function test_7_guru_nonaktif_tidak_dapat_ditugaskan(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $inactiveGuru] = $this->createGuruUser('Guru Inactive', false);

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$inactiveGuru->id],
        ]);

        $response->assertSessionHasErrors('guru');
        $this->assertDatabaseMissing('jadwal_piket', [
            'jam_mulai' => '07:00:00',
        ]);
    }

    /**
     * Test 8: Minimal satu Guru wajib dipilih.
     */
    public function test_8_minimal_satu_guru_wajib_dipilih(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [],
        ]);

        $response->assertSessionHasErrors('guru');
    }

    /**
     * Test 9: Jam mulai harus lebih kecil dari jam selesai.
     */
    public function test_9_jam_mulai_harus_lebih_kecil_dari_jam_selesai(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Aktif');

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '10:00',
            'jam_selesai' => '08:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$guru->id],
        ]);

        $response->assertSessionHasErrors('jam_selesai');
    }

    /**
     * Test 10: Tanggal selesai tidak boleh sebelum tanggal mulai.
     */
    public function test_10_tanggal_selesai_tidak_boleh_sebelum_tanggal_mulai(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Aktif');

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-07-01',
            'tanggal_selesai_berlaku' => '2026-06-01',
            'guru' => [$guru->id],
        ]);

        $response->assertSessionHasErrors('tanggal_selesai_berlaku');
    }

    /**
     * Test 11: Jadwal overlap ditolak (07:00-09:30 vs 08:00-10:00 ditolak).
     */
    public function test_11_jadwal_overlap_ditolak(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru A');

        // Existing schedule: Senin 07:00 - 09:30 berlaku sepanjang 2026
        $this->createJadwal(1, '07:00:00', '09:30:00');

        // Try to create overlapping: Senin 08:00 - 10:00
        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$guru->id],
        ]);

        $response->assertSessionHasErrors('jam_mulai');
    }

    /**
     * Test 12: Boundary: 07:00 - 09:30 dan 09:30 - 12:00 diperbolehkan.
     */
    public function test_12_boundary_jam_selesai_sama_dengan_jam_mulai_diperbolehkan(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru B');

        // Existing: 07:00 - 09:30
        $this->createJadwal(1, '07:00:00', '09:30:00');

        // New boundary session: 09:30 - 12:00
        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '09:30',
            'jam_selesai' => '12:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$guru->id],
        ]);

        $response->assertRedirect(route('admin.jadwal-piket.index'));
        $this->assertDatabaseHas('jadwal_piket', [
            'hari' => 1,
            'jam_mulai' => '09:30:00',
            'jam_selesai' => '12:00:00',
        ]);
    }

    /**
     * Test 13: Admin dapat edit jadwal.
     */
    public function test_13_admin_dapat_edit_jadwal(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser1, $guru1] = $this->createGuruUser('Guru Asli');
        [$guruUser2, $guru2] = $this->createGuruUser('Guru Baru');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        $jadwal->guru()->sync([$guru1->id]);

        $response = $this->actingAs($admin)->put(route('admin.jadwal-piket.update', $jadwal), [
            'hari' => 3, // Ubah ke Rabu
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:30',
            'tanggal_mulai_berlaku' => '2026-02-01',
            'tanggal_selesai_berlaku' => '2026-11-30',
            'guru' => [$guru2->id],
        ]);

        $response->assertRedirect(route('admin.jadwal-piket.index'));
        $this->assertDatabaseHas('jadwal_piket', [
            'id' => $jadwal->id,
            'hari' => 3,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:30:00',
        ]);

        // Verifikasi penugasan baru
        $this->assertDatabaseHas('jadwal_piket_guru', [
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guru2->id,
        ]);
        $this->assertDatabaseMissing('jadwal_piket_guru', [
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guru1->id,
        ]);
    }

    /**
     * Test 14: Edit assignment guru tidak menghapus record Guru.
     */
    public function test_14_edit_assignment_guru_tidak_menghapus_record_guru(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser1, $guru1] = $this->createGuruUser('Guru Satu');
        [$guruUser2, $guru2] = $this->createGuruUser('Guru Dua');

        $jadwal = $this->createJadwal(1);
        $jadwal->guru()->sync([$guru1->id]);

        $guruCountBefore = Guru::count();

        // Ganti penugasan dari guru1 ke guru2
        $this->actingAs($admin)->put(route('admin.jadwal-piket.update', $jadwal), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$guru2->id],
        ]);

        // Record guru di tabel guru TIDAK terhapus
        $this->assertEquals($guruCountBefore, Guru::count());
        $this->assertDatabaseHas('guru', ['id' => $guru1->id]);
        $this->assertDatabaseHas('guru', ['id' => $guru2->id]);
    }

    /**
     * Test 15: Admin dapat menonaktifkan jadwal.
     */
    public function test_15_admin_dapat_menonaktifkan_jadwal(): void
    {
        $admin = $this->createAdminUser();
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00', true);

        $response = $this->actingAs($admin)->patch(route('admin.jadwal-piket.toggle', $jadwal));

        $response->assertRedirect();
        $this->assertDatabaseHas('jadwal_piket', [
            'id' => $jadwal->id,
            'is_active' => false,
        ]);

        // Toggle kembali ke aktif
        $this->actingAs($admin)->patch(route('admin.jadwal-piket.toggle', $jadwal));
        $this->assertDatabaseHas('jadwal_piket', [
            'id' => $jadwal->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test 16: Jadwal nonaktif tidak digunakan oleh GuruPiketService.
     */
    public function test_16_jadwal_nonaktif_tidak_digunakan_oleh_guru_piket_service(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta')); // Senin 08:00

        [$guruUser, $guru] = $this->createGuruUser('Guru Piket Nonaktif');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00', false); // Nonaktif
        $jadwal->guru()->sync([$guru->id]);

        $piketService = app(GuruPiketService::class);
        $activeJadwal = $piketService->getJadwalAktif();

        $this->assertNull($activeJadwal);
    }

    /**
     * Test 17: Jadwal aktif tetap terbaca oleh GuruPiketService.
     */
    public function test_17_jadwal_aktif_tetap_terbaca_oleh_guru_piket_service(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta')); // Senin 08:00

        [$guruUser, $guru] = $this->createGuruUser('Guru Piket Aktif');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00', true); // Aktif
        $jadwal->guru()->sync([$guru->id]);

        $piketService = app(GuruPiketService::class);
        $activeJadwal = $piketService->getJadwalAktif();

        $this->assertNotNull($activeJadwal);
        $this->assertEquals($jadwal->id, $activeJadwal->id);
    }

    /**
     * Test 18: Guru yang ditugaskan muncul sebagai Guru Resmi.
     */
    public function test_18_guru_yang_ditugaskan_muncul_sebagai_guru_resmi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$guruUser, $guru] = $this->createGuruUser('Guru Resmi Terdaftar');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00', true);
        $jadwal->guru()->sync([$guru->id]);

        $piketService = app(GuruPiketService::class);
        $info = $piketService->getInformasiSesi();

        $this->assertEquals('Berlangsung', $info['status_sesi']);
        $this->assertCount(1, $info['petugas']);
        $this->assertEquals('Guru Resmi Terdaftar', $info['petugas'][0]['guru']->nama_lengkap);
    }

    /**
     * Test 19: Pertukaran jadwal existing tidak terhapus ketika jadwal dilihat/edit.
     */
    public function test_19_pertukaran_jadwal_existing_tidak_terhapus_ketika_jadwal_dilihat_atau_edit(): void
    {
        $admin = $this->createAdminUser();
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');

        $jadwal = $this->createJadwal(1);
        $jadwal->guru()->sync([$guruA->id]);

        $swap = PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        // 1. Show jadwal
        $this->actingAs($admin)->get(route('admin.jadwal-piket.show', $jadwal))->assertOk();

        // 2. Edit jadwal
        $this->actingAs($admin)->put(route('admin.jadwal-piket.update', $jadwal), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$guruA->id],
        ]);

        // Pertukaran record tetap utuh
        $this->assertDatabaseHas('pertukaran_jadwal_piket', [
            'id' => $swap->id,
            'status' => 'disetujui',
            'is_active' => true,
        ]);
    }

    /**
     * Test 20: Request tidak dapat memanipulasi field sensitif.
     */
    public function test_20_request_tidak_dapat_memanipulasi_field_sensitif(): void
    {
        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Normal');

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$guru->id],
            // Malicious payload attempts
            'id' => 99999,
            'is_admin' => true,
            'role' => 'superadmin',
        ]);

        $response->assertRedirect(route('admin.jadwal-piket.index'));
        $this->assertDatabaseMissing('jadwal_piket', [
            'id' => 99999,
        ]);
    }

    /**
     * Test 21: Semua guru aktif dapat dimuat pada form tanpa limit hardcoded.
     */
    public function test_21_semua_guru_aktif_dapat_dimuat_pada_form_tanpa_hardcoded_limit(): void
    {
        $admin = $this->createAdminUser();

        // Buat 75 guru aktif dan 5 guru nonaktif
        for ($i = 1; $i <= 75; $i++) {
            $this->createGuruUser("Guru Aktif {$i}", true);
        }
        for ($j = 1; $j <= 5; $j++) {
            $this->createGuruUser("Guru Nonaktif {$j}", false);
        }

        $response = $this->actingAs($admin)->get(route('admin.jadwal-piket.create'));

        $response->assertStatus(200);
        $response->assertViewHas('gurus', function ($gurus) {
            return $gurus->count() === 75;
        });
        $response->assertSee('75 Guru aktif tersedia');
        $response->assertDontSee('Guru Nonaktif 1');
    }

    /**
     * Test 22: Guru spesifik (misal Akhmad Lutfianto) dan NIP dapat dipilih dan disimpan.
     */
    public function test_22_guru_spesifik_berdasarkan_nama_dan_nip_dapat_dipilih(): void
    {
        $admin = $this->createAdminUser();

        $unique = rand(100000, 999999);
        $user = User::create([
            'name' => 'Akhmad Lutfianto',
            'email' => "akhmad_{$unique}@sch.id",
            'role' => 'guru',
            'nis_nip' => '198501012010011001',
            'password' => Hash::make('password'),
        ]);
        $akhmad = Guru::create([
            'user_id' => $user->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Akhmad Lutfianto',
            'status_aktif' => true,
        ]);

        [$guruUser2, $fitria] = $this->createGuruUser('Fitria Diana Kumala Sari');

        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-07-20',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$akhmad->id, $fitria->id],
        ]);

        $response->assertRedirect(route('admin.jadwal-piket.index'));

        $jadwal = JadwalPiket::where('hari', 1)->first();
        $this->assertNotNull($jadwal);
        $this->assertEquals(2, $jadwal->guru()->count());
        $this->assertTrue($jadwal->guru->contains('id', $akhmad->id));
        $this->assertTrue($jadwal->guru->contains('id', $fitria->id));
    }

    /**
     * Test 23: Pilihan hari Senin (1) dan Jumat (5) tersimpan dengan benar.
     */
    public function test_23_pilihan_hari_senin_dan_jumat_menghasilkan_value_sesuai(): void
    {
        $admin = $this->createAdminUser();
        [$user, $guru] = $this->createGuruUser('Guru Hari Test');

        // Simpan Senin (1)
        $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$guru->id],
        ])->assertRedirect(route('admin.jadwal-piket.index'));

        $this->assertDatabaseHas('jadwal_piket', [
            'hari' => 1,
            'jam_mulai' => '07:00:00',
        ]);

        // Simpan Jumat (5)
        $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 5,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'guru' => [$guru->id],
        ])->assertRedirect(route('admin.jadwal-piket.index'));

        $this->assertDatabaseHas('jadwal_piket', [
            'hari' => 5,
            'jam_mulai' => '07:00:00',
        ]);
    }

    /**
     * Test 24: Tanggal mulai dan selesai diperlakukan sebagai periode berulang mingguan.
     */
    public function test_24_tanggal_mulai_dan_selesai_diperlakukan_sebagai_periode_berlaku(): void
    {
        $admin = $this->createAdminUser();
        [$user, $guru] = $this->createGuruUser('Guru Periode');

        // Buat jadwal: Senin 07:00 - 09:30 periode 2026-07-20 s/d 2026-12-31
        $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-07-20',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$guru->id],
        ]);

        $piketService = app(GuruPiketService::class);

        // Minggu ke-1 (Senin 20 Juli 2026) -> AKTIF
        Carbon::setTestNow(Carbon::parse('2026-07-20 08:00:00', 'Asia/Jakarta'));
        $this->assertNotNull($piketService->getJadwalAktif());

        // Minggu ke-2 (Senin 27 Juli 2026) -> TETAP AKTIF
        Carbon::setTestNow(Carbon::parse('2026-07-27 08:00:00', 'Asia/Jakarta'));
        $this->assertNotNull($piketService->getJadwalAktif());

        // Minggu di bulan November (Senin 16 Nov 2026) -> TETAP AKTIF
        Carbon::setTestNow(Carbon::parse('2026-11-16 08:00:00', 'Asia/Jakarta'));
        $this->assertNotNull($piketService->getJadwalAktif());

        // Setelah periode selesai (Senin 4 Januari 2027) -> TIDAK AKTIF
        Carbon::setTestNow(Carbon::parse('2027-01-04 08:00:00', 'Asia/Jakarta'));
        $this->assertNull($piketService->getJadwalAktif());
    }

    /**
     * Test 25: Konektivitas lintas role Admin -> Guru, Siswa, dan Satpam.
     */
    public function test_25_konektivitas_lintas_role_admin_membuat_jadwal_terbaca_di_guru_siswa_dan_satpam(): void
    {
        // Tetapkan waktu Senin 08:00 (Sesi jadwal piket aktif)
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        $admin = $this->createAdminUser();
        [$guruUser, $guru] = $this->createGuruUser('Guru Lintas Role');
        $siswaUser = $this->createSiswaUser();
        $satpamUser = $this->createSatpamUser();

        // 1. Admin membuat jadwal Senin 07:00 - 09:30
        $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$guru->id],
        ])->assertRedirect(route('admin.jadwal-piket.index'));

        $this->flushSession();
        $this->app['auth']->forgetGuards();

        // 2. Guru Piket membuka Dashboard Guru
        $responseGuru = $this->actingAs($guruUser)->get(route('guru.dashboard'));
        $responseGuru->assertStatus(200);
        $responseGuru->assertSee('Guru Piket');
        $responseGuru->assertSee('Guru Lintas Role');

        // 3. Siswa membuka Dashboard Siswa
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $responseSiswa = $this->actingAs($siswaUser)->get(route('siswa.dashboard'));
        $responseSiswa->assertStatus(200);
        $responseSiswa->assertSee('Guru Lintas Role');

        // 4. Satpam membuka Dashboard Satpam
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $responseSatpam = $this->actingAs($satpamUser)->get(route('satpam.dashboard'));
        $responseSatpam->assertStatus(200);
        $responseSatpam->assertSee('Guru Lintas Role');
    }

    /**
     * Test 26: Admin dapat mengatur nama sesi, koordinator, dan menampilkan No HP guru.
     */
    public function test_26_admin_dapat_mengatur_nama_sesi_dan_koordinator_serta_melihat_no_hp(): void
    {
        $admin = $this->createAdminUser();

        [$kUser, $koordinator] = $this->createGuruUser('Almu\'idul \'Afwan, S.Pd.');
        $koordinator->update(['no_telepon' => '085741322231']);

        [$gUser1, $guru1] = $this->createGuruUser('Akhmad Lutfianto, S.Pd.');
        $guru1->update(['no_telepon' => '082327046669']);

        [$gUser2, $guru2] = $this->createGuruUser('Fitria Diana Kumala Sari, S.Pd.');
        $guru2->update(['no_telepon' => '085293146052']);

        // 1. Simpan jadwal Sesi 1 dengan Koordinator dan No HP
        $response = $this->actingAs($admin)->post(route('admin.jadwal-piket.store'), [
            'hari' => 1,
            'nama_sesi' => 'Sesi 1',
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'koordinator_guru_id' => $koordinator->id,
            'tanggal_mulai_berlaku' => '2026-07-20',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'guru' => [$guru1->id, $guru2->id],
        ]);

        $response->assertRedirect(route('admin.jadwal-piket.index'));

        $this->assertDatabaseHas('jadwal_piket', [
            'hari' => 1,
            'nama_sesi' => 'Sesi 1',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'koordinator_guru_id' => $koordinator->id,
        ]);

        $jadwal = JadwalPiket::where('nama_sesi', 'Sesi 1')->first();
        $this->assertNotNull($jadwal);
        $this->assertEquals($koordinator->id, $jadwal->koordinator_guru_id);

        // 2. Cek halaman Index (tampilan list)
        $responseIndex = $this->actingAs($admin)->get(route('admin.jadwal-piket.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Sesi 1');
        $responseIndex->assertSee('Almu\'idul \'Afwan, S.Pd.');
        $responseIndex->assertSee('085741322231');
        $responseIndex->assertSee('Akhmad Lutfianto, S.Pd.');
        $responseIndex->assertSee('082327046669');

        // 3. Cek halaman Index (tampilan matriks sekolah)
        $responseMatriks = $this->actingAs($admin)->get(route('admin.jadwal-piket.index', ['view' => 'matriks']));
        $responseMatriks->assertStatus(200);
        $responseMatriks->assertSee('Matriks Jadwal Sekolah');
        $responseMatriks->assertSee('Almu\'idul \'Afwan, S.Pd.');
        $responseMatriks->assertSee('085741322231');
        $responseMatriks->assertSee('Akhmad Lutfianto, S.Pd.');
        $responseMatriks->assertSee('082327046669');

        // 4. Cek halaman Detail (show)
        $responseShow = $this->actingAs($admin)->get(route('admin.jadwal-piket.show', $jadwal));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Sesi 1');
        $responseShow->assertSee('Almu\'idul \'Afwan, S.Pd.');
        $responseShow->assertSee('085741322231');
        $responseShow->assertSee('082327046669');
        $responseShow->assertSee('085293146052');
    }
}

