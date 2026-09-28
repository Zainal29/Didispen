<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\PertukaranJadwalPiket;
use App\Models\User;
use App\Services\GuruPiketService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class JadwalPiketTest extends TestCase
{
    use RefreshDatabase;

    protected GuruPiketService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GuruPiketService();
    }

    private function createUser(string $role = 'guru'): User
    {
        $unique = rand(100000, 999999);

        return User::create([
            'name' => 'User ' . $unique,
            'email' => "user_{$unique}@sch.id",
            'role' => $role,
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);
    }

    private function createGuru(array $attributes = []): Guru
    {
        $user = $this->createUser('guru');

        return Guru::create(array_merge([
            'user_id' => $user->id,
            'nip' => 'NIP' . rand(100000, 999999),
            'nama_lengkap' => 'Guru ' . rand(100, 999),
            'status_aktif' => true,
        ], $attributes));
    }

    /**
     * Test Fondasi: Jadwal dapat dibuat
     */
    public function test_jadwal_piket_dapat_dibuat(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'tanggal_selesai_berlaku' => null,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('jadwal_piket', [
            'id' => $jadwal->id,
            'hari' => 1,
            'is_active' => true,
        ]);
        $this->assertEquals('07:00', substr($jadwal->jam_mulai, 0, 5));
        $this->assertEquals('09:30', substr($jadwal->jam_selesai, 0, 5));
    }

    /**
     * Test Fondasi: Duplicate assignment ditolak
     */
    public function test_duplicate_assignment_guru_pada_jadwal_yang_sama_ditolak(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        JadwalPiketGuru::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guru->id,
        ]);

        $this->expectException(QueryException::class);
        JadwalPiketGuru::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guru->id,
        ]);
    }

    /**
     * Test Fondasi: Guru dapat berada di beberapa jadwal
     */
    public function test_guru_dapat_berada_di_beberapa_jadwal(): void
    {
        $jadwalSenin = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '09:30',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $jadwalKamis = JadwalPiket::create([
            'hari' => 4,
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSenin->id, 'guru_id' => $guru->id]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalKamis->id, 'guru_id' => $guru->id]);

        $this->assertCount(2, $guru->jadwalPiketGurus);
    }

    /**
     * Test 1: Jadwal aktif ditemukan berdasarkan hari dan waktu
     * Interval: jam_mulai <= waktu < jam_selesai
     */
    public function test_1_jadwal_aktif_ditemukan_berdasarkan_hari_dan_waktu(): void
    {
        // 2026-09-28 adalah hari Senin (ISO 1)
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        // 07:00 -> masuk
        $aktifPukul7 = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 07:00:00'));
        $this->assertNotNull($aktifPukul7);
        $this->assertEquals($jadwal->id, $aktifPukul7->id);

        // 09:29 -> masih masuk
        $aktifPukul929 = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 09:29:59'));
        $this->assertNotNull($aktifPukul929);
        $this->assertEquals($jadwal->id, $aktifPukul929->id);
    }

    /**
     * Test 2: Jadwal sebelum jam mulai dapat ditemukan sebagai jadwal berikutnya
     */
    public function test_2_jadwal_sebelum_jam_mulai_dapat_ditemukan_sebagai_jadwal_berikutnya(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        // Pukul 06:30 belum masuk jadwal aktif
        $aktif = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 06:30:00'));
        $this->assertNull($aktif);

        // Tetapi dapat ditemukan sebagai jadwal berikutnya
        $berikutnya = $this->service->getJadwalBerikutnya(Carbon::parse('2026-09-28 06:30:00'));
        $this->assertNotNull($berikutnya);
        $this->assertEquals($jadwal->id, $berikutnya->id);
    }

    /**
     * Test 3: Jadwal setelah jam selesai tidak dianggap sedang berlangsung
     */
    public function test_3_jadwal_setelah_jam_selesai_tidak_dianggap_sedang_berlangsung(): void
    {
        JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        // Tepat 09:30 sudah keluar dari sesi
        $aktifPukul930 = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 09:30:00'));
        $this->assertNull($aktifPukul930);

        // Pukul 09:31 juga tidak aktif
        $aktifPukul931 = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 09:31:00'));
        $this->assertNull($aktifPukul931);
    }

    /**
     * Test 4: Jadwal di luar masa berlaku tidak ditemukan
     */
    public function test_4_jadwal_di_luar_masa_berlaku_tidak_ditemukan(): void
    {
        // Jadwal berlaku 1 Agustus 2026 - 31 Agustus 2026
        $jadwalExpired = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-08-01',
            'tanggal_selesai_berlaku' => '2026-08-31',
            'is_active' => true,
        ]);

        // Cek pada tanggal 28 September 2026 (sudah expired)
        $aktif = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 08:00:00'));
        $this->assertNull($aktif);

        // Cek jika is_active = false
        $jadwalExpired->update([
            'tanggal_selesai_berlaku' => null,
            'is_active' => false,
        ]);
        $aktifInaktif = $this->service->getJadwalAktif(Carbon::parse('2026-09-28 08:00:00'));
        $this->assertNull($aktifInaktif);
    }

    /**
     * Test 5: Semua guru resmi ditemukan
     */
    public function test_5_semua_guru_resmi_ditemukan(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru1 = $this->createGuru(['nama_lengkap' => 'Guru A']);
        $guru2 = $this->createGuru(['nama_lengkap' => 'Guru B']);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru1->id]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru2->id]);

        $guruResmi = $this->service->getGuruResmi($jadwal);

        $this->assertCount(2, $guruResmi);
        $this->assertContains($guru1->id, $guruResmi->pluck('id'));
        $this->assertContains($guru2->id, $guruResmi->pluck('id'));
    }

    /**
     * Test 6: Replacement aktif menggantikan guru asal
     */
    public function test_6_replacement_aktif_menggantikan_guru_asal(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruAsal = $this->createGuru(['nama_lengkap' => 'Guru Asal']);
        $guruTetap = $this->createGuru(['nama_lengkap' => 'Guru Tetap']);
        $guruPengganti = $this->createGuru(['nama_lengkap' => 'Guru Pengganti']);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruTetap->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $result = $this->service->getPetugasAktual($jadwal, '2026-09-28');
        $this->assertFalse($result['conflict']);

        $petugasIds = collect($result['petugas'])->pluck('guru.id')->all();

        // Guru Pengganti dan Guru Tetap hadir, Guru Asal digantikan
        $this->assertContains($guruPengganti->id, $petugasIds);
        $this->assertContains($guruTetap->id, $petugasIds);
        $this->assertNotContains($guruAsal->id, $petugasIds);
    }

    /**
     * Test 7: Replacement tidak mengubah jadwal resmi
     */
    public function test_7_replacement_tidak_mengubah_jadwal_resmi(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruAsal = $this->createGuru();
        $guruPengganti = $this->createGuru();

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        // Cek master jadwal_piket_guru tetap Guru Asal
        $guruResmi = $this->service->getGuruResmi($jadwal);
        $this->assertCount(1, $guruResmi);
        $this->assertEquals($guruAsal->id, $guruResmi->first()->id);
    }

    /**
     * Test 8: Guru pengganti tidak harus memiliki jadwal piket sendiri
     */
    public function test_8_guru_pengganti_tidak_harus_memiliki_jadwal_piket_sendiri(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruAsal = $this->createGuru();
        // Guru pengganti murni tanpa jadwal piket
        $guruPenggantiTanpaJadwal = $this->createGuru(['status_aktif' => true]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPenggantiTanpaJadwal->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $result = $this->service->getPetugasAktual($jadwal, '2026-09-28');
        $petugasIds = collect($result['petugas'])->pluck('guru.id')->all();

        $this->assertContains($guruPenggantiTanpaJadwal->id, $petugasIds);
        $this->assertCount(0, $guruPenggantiTanpaJadwal->jadwalPiketGurus);
    }

    /**
     * Test 9: Replacement history lama tidak dianggap aktif
     */
    public function test_9_replacement_history_lama_tidak_dianggap_aktif(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruAsal = $this->createGuru();
        $penggantiLama = $this->createGuru();
        $penggantiBaru = $this->createGuru();

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        // Replacement lama (is_active = false)
        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $penggantiLama->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => false,
        ]);

        // Replacement baru (is_active = true)
        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $penggantiBaru->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $result = $this->service->getPetugasAktual($jadwal, '2026-09-28');
        $petugasIds = collect($result['petugas'])->pluck('guru.id')->all();

        $this->assertContains($penggantiBaru->id, $petugasIds);
        $this->assertNotContains($penggantiLama->id, $petugasIds);
    }

    /**
     * Test 10: Conflict multiple active replacement tidak memilih otomatis
     */
    public function test_10_conflict_multiple_active_replacement_tidak_memilih_otomatis(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruAsal = $this->createGuru();
        $penggantiA = $this->createGuru();
        $penggantiB = $this->createGuru();

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        // Anomali: Ada 2 replacement aktif sekaligus untuk guru asal yang sama pada tanggal yang sama
        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $penggantiA->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $penggantiB->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $result = $this->service->getPetugasAktual($jadwal, '2026-09-28');

        // Harus flag conflict = true dan tidak memilih penggantiA atau penggantiB secara sembarangan
        $this->assertTrue($result['conflict']);
        $this->assertStringContainsString('Conflict', $result['conflict_message']);
        $this->assertEmpty($result['petugas']);
    }

    /**
     * Test 11: Guru dengan checklog 'keluar' -> Sedang Keluar
     */
    public function test_11_guru_dengan_checklog_keluar_menghasilkan_status_sedang_keluar(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        // Checklog keluar aktif
        GuruChecklog::create([
            'guru_id' => $guru->id,
            'alasan' => 'Dinas ke dinas pendidikan',
            'tujuan' => 'Dinas Pendidikan',
            'jam_keluar' => '2026-09-28 08:00:00',
            'status' => 'keluar',
        ]);

        $status = $this->service->resolveStatusPetugas($guru, $jadwal, Carbon::parse('2026-09-28 08:15:00'));
        $this->assertEquals('Sedang Keluar', $status);
    }

    /**
     * Test 12: Guru dengan checklog 'selesai' -> Sudah Kembali
     */
    public function test_12_guru_dengan_checklog_selesai_menghasilkan_status_sudah_kembali(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        // Checklog sudah selesai / kembali
        GuruChecklog::create([
            'guru_id' => $guru->id,
            'alasan' => 'Ke bank sebentar',
            'tujuan' => 'Bank',
            'jam_keluar' => '2026-09-28 08:00:00',
            'jam_kembali' => '2026-09-28 08:20:00',
            'status' => 'selesai',
        ]);

        $status = $this->service->resolveStatusPetugas($guru, $jadwal, Carbon::parse('2026-09-28 08:25:00'));
        $this->assertEquals('Sudah Kembali', $status);
    }

    /**
     * Test 13: Guru tanpa checklog aktif -> Sedang Bertugas
     */
    public function test_13_guru_tanpa_checklog_aktif_menghasilkan_status_sedang_bertugas(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        // Tanpa checklog pada jam sesi piket
        $status = $this->service->resolveStatusPetugas($guru, $jadwal, Carbon::parse('2026-09-28 07:30:00'));
        $this->assertEquals('Sedang Bertugas', $status);
    }

    /**
     * Test 14: Multi-guru memiliki status masing-masing
     */
    public function test_14_multi_guru_memiliki_status_masing_masing(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruA = $this->createGuru(['nama_lengkap' => 'Guru A']);
        $guruB = $this->createGuru(['nama_lengkap' => 'Guru B']);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruB->id]);

        // Guru A sedang keluar
        GuruChecklog::create([
            'guru_id' => $guruA->id,
            'alasan' => 'Tugas dinas',
            'tujuan' => 'Kantor Cabdin',
            'jam_keluar' => '2026-09-28 08:00:00',
            'status' => 'keluar',
        ]);

        // Guru B tidak keluar

        $infoSesi = $this->service->getInformasiSesi(Carbon::parse('2026-09-28 08:15:00'));

        $this->assertFalse($infoSesi['conflict']);
        $petugasA = collect($infoSesi['petugas'])->firstWhere('guru.id', $guruA->id);
        $petugasB = collect($infoSesi['petugas'])->firstWhere('guru.id', $guruB->id);

        $this->assertEquals('Sedang Keluar', $petugasA['status']);
        $this->assertEquals('Sedang Bertugas', $petugasB['status']);
    }

    /**
     * Test 15: Setelah sesi selesai -> Sesi Selesai
     */
    public function test_15_setelah_sesi_selesai_menghasilkan_status_sesi_selesai(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        $status = $this->service->resolveStatusPetugas($guru, $jadwal, Carbon::parse('2026-09-28 09:31:00'));
        $this->assertEquals('Sesi Selesai', $status);
    }

    /**
     * Test 16: Sebelum sesi -> Akan Bertugas
     */
    public function test_16_sebelum_sesi_menghasilkan_status_akan_bertugas(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();

        $status = $this->service->resolveStatusPetugas($guru, $jadwal, Carbon::parse('2026-09-28 06:50:00'));
        $this->assertEquals('Akan Bertugas', $status);
    }

    /**
     * Test FK & Nullable Behavioral integrity
     */
    public function test_nullable_dan_delete_behavior_fk(): void
    {
        $admin = $this->createUser('admin');
        $guruAsal = $this->createGuru();
        $guruPengganti = $this->createGuru();

        $jadwal = JadwalPiket::create([
            'hari' => 3,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'tanggal_selesai_berlaku' => null,
            'is_active' => true,
        ]);

        $pivot = JadwalPiketGuru::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_id' => $guruAsal->id,
        ]);

        $pertukaran = PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'alasan' => null,
            'status' => 'disetujui',
            'is_active' => true,
            'diproses_oleh' => $admin->id,
        ]);

        // Null on delete untuk user processor
        $admin->delete();
        $this->assertNull($pertukaran->fresh()->diproses_oleh);

        // Restrict on delete untuk guru
        try {
            $guruPengganti->delete();
            $this->fail('Harusnya gagal restrict on delete guru.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        // Cascade delete pivot saat jadwal dihapus
        $pertukaran->delete();
        $jadwal->delete();
        $this->assertDatabaseMissing('jadwal_piket_guru', ['id' => $pivot->id]);
    }

    /**
     * Regression Test 1: Guru pengganti nonaktif tidak valid menjadi petugas aktual
     */
    public function test_guru_pengganti_nonaktif_tidak_valid_menjadi_petugas_aktual(): void
    {
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guruAsal = $this->createGuru(['nama_lengkap' => 'Guru Asal Resmi', 'status_aktif' => true]);
        // Guru pengganti nonaktif
        $guruPenggantiNonaktif = $this->createGuru(['nama_lengkap' => 'Guru Pengganti Nonaktif', 'status_aktif' => false]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPenggantiNonaktif->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $result = $this->service->getPetugasAktual($jadwal, '2026-09-28');
        $this->assertFalse($result['conflict']);

        $petugasIds = collect($result['petugas'])->pluck('guru.id')->all();

        // Guru pengganti nonaktif DITOLAK menjadi petugas aktual
        $this->assertNotContains($guruPenggantiNonaktif->id, $petugasIds);
        // Guru resmi tetap bertugas
        $this->assertContains($guruAsal->id, $petugasIds);

        // Jadwal resmi tidak berubah
        $guruResmi = $this->service->getGuruResmi($jadwal);
        $this->assertEquals($guruAsal->id, $guruResmi->first()->id);
    }

    /**
     * Regression Test 2: Checklog selesai di luar periode sesi tidak memengaruhi status sesi yang sedang dihitung
     */
    public function test_checklog_selesai_di_luar_periode_sesi_tidak_membuat_sesi_sekarang_sudah_kembali(): void
    {
        // Sesi Siang: 13:00 - 15:00
        $jadwalSiang = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '15:00:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        $guru = $this->createGuru();
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSiang->id, 'guru_id' => $guru->id]);

        // Checklog pagi hari (08:00 - 08:30) sudah selesai
        GuruChecklog::create([
            'guru_id' => $guru->id,
            'alasan' => 'Urusan pagi',
            'tujuan' => 'Keperluan Luar',
            'jam_keluar' => '2026-09-28 08:00:00',
            'jam_kembali' => '2026-09-28 08:30:00',
            'status' => 'selesai',
        ]);

        // Pada jam 13:30 (Sesi Siang berlangsung), guru belum pernah keluar pada sesi siang
        // Status harus "Sedang Bertugas", BUKAN "Sudah Kembali"
        $statusSiang = $this->service->resolveStatusPetugas($guru, $jadwalSiang, Carbon::parse('2026-09-28 13:30:00'));
        $this->assertEquals('Sedang Bertugas', $statusSiang);

        // Jika guru di sesi siang keluar pada 13:40 dan kembali 14:00 (selesai)
        GuruChecklog::create([
            'guru_id' => $guru->id,
            'alasan' => 'Beli ATK siang',
            'tujuan' => 'Toko Buku',
            'jam_keluar' => '2026-09-28 13:40:00',
            'jam_kembali' => '2026-09-28 14:00:00',
            'status' => 'selesai',
        ]);

        // Pada jam 14:05, barulah statusnya menjadi "Sudah Kembali"
        $statusSetelahKembali = $this->service->resolveStatusPetugas($guru, $jadwalSiang, Carbon::parse('2026-09-28 14:05:00'));
        $this->assertEquals('Sudah Kembali', $statusSetelahKembali);
    }
}
