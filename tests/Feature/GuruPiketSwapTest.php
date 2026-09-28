<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\PertukaranJadwalPiket;
use App\Models\User;
use App\Services\GuruPiketService;
use App\Services\GuruPiketSwapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GuruPiketSwapTest extends TestCase
{
    use RefreshDatabase;

    protected GuruPiketSwapService $swapService;
    protected GuruPiketService $piketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->swapService = app(GuruPiketSwapService::class);
        $this->piketService = app(GuruPiketService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createGuruUser(string $name, array $guruAttributes = []): array
    {
        $unique = rand(100000, 999999);

        $user = User::create([
            'name' => $name,
            'email' => "guru_{$unique}@sch.id",
            'role' => 'guru',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        $guru = Guru::create(array_merge([
            'user_id' => $user->id,
            'nip' => (string) $unique,
            'nama_lengkap' => $name,
            'status_aktif' => true,
        ], $guruAttributes));

        return [$user, $guru];
    }

    private function createJadwal(int $hari = 1, string $jamMulai = '07:00:00', string $jamSelesai = '09:30:00'): JadwalPiket
    {
        return JadwalPiket::create([
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'tanggal_mulai_berlaku' => '2026-01-01',
            'tanggal_selesai_berlaku' => '2026-12-31',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Guru resmi dapat membuat request replacement
     */
    public function test_1_guru_resmi_dapat_membuat_request_replacement(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = $this->createJadwal(1); // Senin
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $response = $this->actingAs($userA)->post(route('guru.piket.swap.store'), [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28', // Senin
            'alasan' => 'Dinas luar',
        ]);

        $response->assertRedirect(route('guru.piket.swap.incoming'));
        $this->assertDatabaseHas('pertukaran_jadwal_piket', [
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'status' => 'menunggu',
            'is_active' => false,
        ]);
    }

    /**
     * Test 2: Guru yang bukan anggota jadwal tidak dapat membuat request
     */
    public function test_2_guru_yang_bukan_anggota_jadwal_tidak_dapat_membuat_request(): void
    {
        [$userBukanAnggota, $guruBukanAnggota] = $this->createGuruUser('Bukan Anggota');
        [$userPengganti, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = $this->createJadwal(1); // Senin

        $response = $this->actingAs($userBukanAnggota)->post(route('guru.piket.swap.store'), [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'alasan' => 'Test bukan anggota',
        ]);

        $response->assertSessionHasErrors('jadwal_piket_id');
        $this->assertDatabaseCount('pertukaran_jadwal_piket', 0);
    }

    /**
     * Test 3: Guru pengganti harus aktif
     */
    public function test_3_guru_pengganti_harus_aktif(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Aktif', ['status_aktif' => true]);

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $swap = $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'alasan' => 'Guru aktif valid',
        ]);

        $this->assertInstanceOf(PertukaranJadwalPiket::class, $swap);
        $this->assertEquals($guruPengganti->id, $swap->guru_pengganti_id);
    }

    /**
     * Test 4: Guru pengganti yang nonaktif ditolak
     */
    public function test_4_guru_pengganti_yang_nonaktif_ditolak(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruNonaktif] = $this->createGuruUser('Guru Nonaktif', ['status_aktif' => false]);

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $this->expectException(ValidationException::class);
        $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruNonaktif->id,
            'tanggal' => '2026-09-28',
        ]);
    }

    /**
     * Test 5: Guru asal dan pengganti tidak boleh sama
     */
    public function test_5_guru_asal_dan_pengganti_tidak_boleh_sama(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $this->expectException(ValidationException::class);
        $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruAsal->id,
            'tanggal' => '2026-09-28',
        ]);
    }

    /**
     * Test 6: Tanggal harus sesuai hari jadwal
     */
    public function test_6_tanggal_harus_sesuai_hari_jadwal(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = $this->createJadwal(1); // Jadwal hari Senin (ISO 1)
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        // 2026-09-29 adalah hari Selasa (ISO 2) -> tidak cocok dengan hari jadwal
        $this->expectException(ValidationException::class);
        $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-29',
        ]);
    }

    /**
     * Test 7: Jadwal harus aktif dan berlaku pada tanggal tersebut
     */
    public function test_7_jadwal_harus_aktif_dan_berlaku_pada_tanggal_tersebut(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        // Jadwal berlaku 2026-01-01 s/d 2026-06-30
        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'tanggal_selesai_berlaku' => '2026-06-30',
            'is_active' => true,
        ]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        // 2026-09-28 adalah hari Senin tetapi di luar masa berlaku
        $this->expectException(ValidationException::class);
        $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
        ]);
    }

    /**
     * Test 8: Request pertama dapat dibuat
     */
    public function test_8_request_pertama_dapat_dibuat(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $swap = $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'alasan' => 'Permintaan pertama',
        ]);

        $this->assertNotNull($swap->id);
        $this->assertEquals('menunggu', $swap->status);
        $this->assertFalse($swap->is_active);
        $this->assertNotNull($swap->diminta_at);
    }

    /**
     * Test 9: Request duplicate yang aktif atau pending ditolak
     */
    public function test_9_request_duplicate_yang_aktif_atau_pending_ditolak(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruB] = $this->createGuruUser('Guru B');
        [$userC, $guruC] = $this->createGuruUser('Guru C');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        // Request pertama (status = menunggu)
        $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);

        // Request kedua untuk sesi dan tanggal yang sama harus ditolak
        $this->expectException(ValidationException::class);
        $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruC->id,
            'tanggal' => '2026-09-28',
        ]);
    }

    /**
     * Test 10: Request dapat ditolak oleh guru pengganti
     */
    public function test_10_request_dapat_ditolak(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $swap = $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
        ]);

        $this->actingAs($userB)->post(route('guru.piket.swap.reject', $swap->id), [
            'catatan' => 'Maaf ada jadwal mengajar lain',
        ]);

        $swap->refresh();
        $this->assertEquals('ditolak', $swap->status);
        $this->assertFalse($swap->is_active);
        $this->assertEquals($userB->id, $swap->diproses_oleh);
        $this->assertNotNull($swap->diproses_at);
        $this->assertEquals('Maaf ada jadwal mengajar lain', $swap->catatan);
    }

    /**
     * Test 11: Request dapat dibatalkan sesuai authorization (oleh guru asal)
     */
    public function test_11_request_dapat_dibatalkan_sesuai_authorization(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');
        [$userLain, $guruLain] = $this->createGuruUser('Guru Lain');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $swap = $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
        ]);

        // Guru lain tidak boleh membatalkan
        $this->actingAs($userLain)->post(route('guru.piket.swap.cancel', $swap->id));
        $swap->refresh();
        $this->assertEquals('menunggu', $swap->status);

        // Guru asal boleh membatalkan
        $this->flushSession();
        $res = $this->actingAs($userA)->post(route('guru.piket.swap.cancel', $swap->id));
        $swap->refresh();
        $this->assertEquals('dibatalkan', $swap->status);
        $this->assertFalse($swap->is_active);
    }

    /**
     * Test 12: Approval membuat status disetujui dan is_active true
     */
    public function test_12_approval_membuat_status_disetujui_dan_is_active_true(): void
    {
        [$userA, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userB, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        $swap = $this->swapService->createSwapRequest($guruAsal, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
        ]);

        $this->actingAs($userB)->post(route('guru.piket.swap.accept', $swap->id));

        $swap->refresh();
        $this->assertEquals('disetujui', $swap->status);
        $this->assertTrue($swap->is_active);
        $this->assertEquals($userB->id, $swap->diproses_oleh);
        $this->assertNotNull($swap->diproses_at);
    }

    /**
     * Test 13: Replacement aktif mengubah Petugas Aktual
     */
    public function test_13_replacement_aktif_mengubah_petugas_aktual(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $swap = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);

        $this->swapService->acceptSwapRequest($swap, $userB);

        $petugasData = $this->piketService->getPetugasAktual($jadwal, '2026-09-28');

        $this->assertFalse($petugasData['conflict']);
        $this->assertCount(1, $petugasData['petugas']);
        $this->assertEquals($guruB->id, $petugasData['petugas'][0]['guru']->id);
        $this->assertTrue($petugasData['petugas'][0]['is_pengganti']);
    }

    /**
     * Test 14: Guru asal hilang dari Petugas Aktual
     */
    public function test_14_guru_asal_hilang_dari_petugas_aktual(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');
        [$userC, $guruC] = $this->createGuruUser('Guru C');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruC->id]);

        $swap = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);
        $this->swapService->acceptSwapRequest($swap, $userB);

        $petugasData = $this->piketService->getPetugasAktual($jadwal, '2026-09-28');
        $guruIds = collect($petugasData['petugas'])->pluck('guru.id')->all();

        $this->assertNotContains($guruA->id, $guruIds);
    }

    /**
     * Test 15: Guru pengganti masuk sebagai Petugas Aktual
     */
    public function test_15_guru_pengganti_masuk_sebagai_petugas_aktual(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $swap = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);
        $this->swapService->acceptSwapRequest($swap, $userB);

        $petugasData = $this->piketService->getPetugasAktual($jadwal, '2026-09-28');
        $guruIds = collect($petugasData['petugas'])->pluck('guru.id')->all();

        $this->assertContains($guruB->id, $guruIds);
    }

    /**
     * Test 16: Guru pengganti tanpa jadwal resmi tetap dapat menjadi Petugas Aktual
     */
    public function test_16_guru_pengganti_tanpa_jadwal_resmi_tetap_dapat_menjadi_petugas_aktual(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        // Guru pengganti ini tidak pernah dimasukkan ke jadwal_piket_guru mana pun
        [$userB, $guruB] = $this->createGuruUser('Guru B Tanpa Jadwal');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $swap = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);
        $this->swapService->acceptSwapRequest($swap, $userB);

        $petugasData = $this->piketService->getPetugasAktual($jadwal, '2026-09-28');

        $this->assertCount(1, $petugasData['petugas']);
        $this->assertEquals($guruB->id, $petugasData['petugas'][0]['guru']->id);
    }

    /**
     * Test 17: Replacement yang inactive tidak digunakan resolver
     */
    public function test_17_replacement_yang_inactive_tidak_digunakan_resolver(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // Buat replacement inactive (is_active = false)
        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => false,
        ]);

        $petugasData = $this->piketService->getPetugasAktual($jadwal, '2026-09-28');

        // Resolver harus tetap menggunakan Guru A (guru resmi)
        $this->assertEquals($guruA->id, $petugasData['petugas'][0]['guru']->id);
        $this->assertFalse($petugasData['petugas'][0]['is_pengganti']);
    }

    /**
     * Test 18: History replacement tidak terhapus
     */
    public function test_18_history_replacement_tidak_terhapus(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');
        [$userC, $guruC] = $this->createGuruUser('Guru C');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // 1. Pengajuan ke Guru B lalu dibatalkan
        $swap1 = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);
        $this->swapService->cancelSwapRequest($swap1, $userA);

        // 2. Pengajuan ke Guru C lalu disetujui
        $swap2 = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruC->id,
            'tanggal' => '2026-09-28',
        ]);
        $this->swapService->acceptSwapRequest($swap2, $userC);

        // Kedua record harus tetap ada di tabel pertukaran_jadwal_piket
        $this->assertDatabaseCount('pertukaran_jadwal_piket', 2);
        $this->assertDatabaseHas('pertukaran_jadwal_piket', [
            'id' => $swap1->id,
            'status' => 'dibatalkan',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('pertukaran_jadwal_piket', [
            'id' => $swap2->id,
            'status' => 'disetujui',
            'is_active' => true,
        ]);
    }

    /**
     * Test 19: Conflict replacement tidak memilih salah satu secara otomatis
     */
    public function test_19_conflict_replacement_tidak_memilih_salah_satu_secara_otomatis(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');
        [$userC, $guruC] = $this->createGuruUser('Guru C');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // Manipulasi database membuat 2 replacement aktif sekaligus (simulasi corrupted/concurrent state)
        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruC->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        // Resolver tidak boleh memilih salah satu, melainkan mendeteksi conflict
        $petugasData = $this->piketService->getPetugasAktual($jadwal, '2026-09-28');

        $this->assertTrue($petugasData['conflict']);
        $this->assertNotNull($petugasData['conflict_message']);
        $this->assertEmpty($petugasData['petugas']);
    }

    /**
     * Test 20: Guru tidak dapat memanipulasi status, is_active, diproses_oleh, guru_asal_id melalui request HTTP
     */
    public function test_20_guru_tidak_dapat_memanipulasi_status_is_active_diproses_oleh_guru_asal_id_melalui_request_http(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');
        [$userLain, $guruLain] = $this->createGuruUser('Guru Lain');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // User A mencoba mengirim parameter terlarang via HTTP:
        // status = disetujui, is_active = true, diproses_oleh = 999, guru_asal_id = guruLain
        $response = $this->actingAs($userA)->post(route('guru.piket.swap.store'), [
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruLain->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
            'diproses_oleh' => 999,
        ]);

        $response->assertRedirect(route('guru.piket.swap.incoming'));

        // Pastikan atribut yang disimpan adalah hasil kontrol server, bukan dari payload request
        $swap = PertukaranJadwalPiket::first();
        $this->assertNotNull($swap);
        $this->assertEquals($guruA->id, $swap->guru_asal_id, 'guru_asal_id harus berasal dari auth guru login');
        $this->assertEquals('menunggu', $swap->status, 'status harus tetap menunggu');
        $this->assertFalse($swap->is_active, 'is_active harus tetap false');
        $this->assertNull($swap->diproses_oleh, 'diproses_oleh harus tetap null');
    }

    /**
     * Test 21: Audit log tercatat pada action yang relevan
     */
    public function test_21_audit_log_tercatat_pada_action_yang_relevan(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru A');
        [$userB, $guruB] = $this->createGuruUser('Guru B');

        $jadwal = $this->createJadwal(1);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // 1. Request
        $swap = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'alasan' => 'Audit test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'guru_piket_swap_requested',
            'table_name' => 'pertukaran_jadwal_piket',
            'record_id' => $swap->id,
        ]);

        // 2. Approve
        $this->swapService->acceptSwapRequest($swap, $userB);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'guru_piket_swap_approved',
            'table_name' => 'pertukaran_jadwal_piket',
            'record_id' => $swap->id,
        ]);
    }

    /**
     * Test 22: Dashboard Guru tetap menampilkan Petugas Aktual berdasarkan replacement aktif
     */
    public function test_22_dashboard_guru_tetap_menampilkan_petugas_aktual_berdasarkan_replacement_aktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Asli');
        [$userB, $guruB] = $this->createGuruUser('Guru Pengganti Bertugas');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // Aktifkan penggantian Guru A digantikan oleh Guru B
        $swap = $this->swapService->createSwapRequest($guruA, [
            'jadwal_piket_id' => $jadwal->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
        ]);
        $this->swapService->acceptSwapRequest($swap, $userB);

        // Kunjungi dashboard sebagai Guru B
        $response = $this->actingAs($userB)->get(route('guru.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Pengganti Bertugas');
        $response->assertSee('Menggantikan: Guru Asli');
        $response->assertSee('Anda bertugas pada sesi ini');
    }
}
