<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\PertukaranJadwalPiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuruPiketDashboardTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Test 1: Guru yang sedang bertugas dapat melihat informasi Guru Piket
     */
    public function test_1_guru_yang_sedang_bertugas_dapat_melihat_informasi_guru_piket(): void
    {
        // 2026-09-28 adalah hari Senin (ISO 1), pukul 08:00
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Bertugas A');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Guru Piket Hari Ini');
        $response->assertSee('07:00 - 09:30 WIB');
        $response->assertSee('Guru Bertugas A');
        $response->assertSee('Anda bertugas pada sesi ini');
        $response->assertSee('Catat Keluar');
    }

    /**
     * Test 2: Guru yang bukan petugas tetap dapat melihat informasi jadwal yang diperbolehkan
     */
    public function test_2_guru_yang_bukan_petugas_tetap_dapat_melihat_informasi_jadwal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Petugas');
        [$userB, $guruB] = $this->createGuruUser('Guru Non Petugas');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $response = $this->actingAs($userB)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Guru Piket Hari Ini');
        $response->assertSee('Guru Petugas');
        $response->assertSee('Anda tidak bertugas pada sesi ini/hari ini.');
        $response->assertSee('text-red-700', false);
        $response->assertSee('Halo, Guru Non-Petugas!', false);
        $response->assertDontSee('truncate');
        $response->assertDontSee('Anda bertugas pada sesi ini/hari ini.');
    }

    /**
     * Test 3: Petugas aktual dengan replacement ditampilkan sebagai guru pengganti
     */
    public function test_3_petugas_aktual_dengan_replacement_ditampilkan_sebagai_guru_pengganti(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userAsal, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userPengganti, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $response = $this->actingAs($userPengganti)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Guru Pengganti');
        $response->assertSee('Menggantikan: Guru Asal');
        $response->assertSee('Anda bertugas pada sesi ini');
    }

    /**
     * Test 4: Guru asal yang digantikan tidak ditampilkan sebagai petugas aktual
     */
    public function test_4_guru_asal_yang_digantikan_tidak_ditampilkan_sebagai_petugas_aktual(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userAsal, $guruAsal] = $this->createGuruUser('Guru Asal');
        [$userPengganti, $guruPengganti] = $this->createGuruUser('Guru Pengganti');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruPengganti->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        // Guru Asal login ke dashboard
        $response = $this->actingAs($userAsal)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        // Guru Asal tidak boleh diakui bertugas pada sesi ini karena sudah digantikan
        $response->assertSee('Anda tidak bertugas pada sesi ini/hari ini.');
        $response->assertDontSee('Anda bertugas pada sesi ini/hari ini.');
    }

    /**
     * Test 5: Status petugas menggunakan resolver
     */
    public function test_5_status_petugas_menggunakan_resolver(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Status Normal');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sedang Bertugas');
    }

    /**
     * Test 6: Guru dengan checklog 'keluar' ditampilkan 'Sedang Keluar'
     */
    public function test_6_guru_dengan_checklog_keluar_ditampilkan_sedang_keluar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:15:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Keluar Sesi');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        GuruChecklog::create([
            'guru_id' => $guruA->id,
            'alasan' => 'Dinas Pendidikan',
            'tujuan' => 'Kantor Cabdin',
            'jam_keluar' => '2026-09-28 08:00:00',
            'status' => 'keluar',
        ]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sedang Keluar');
    }

    /**
     * Test 7: Guru yang sudah kembali ditampilkan 'Sudah Kembali'
     */
    public function test_7_guru_yang_sudah_kembali_ditampilkan_sudah_kembali(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:25:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Kembali Sesi');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        GuruChecklog::create([
            'guru_id' => $guruA->id,
            'alasan' => 'Ke bank',
            'tujuan' => 'Bank',
            'jam_keluar' => '2026-09-28 08:00:00',
            'jam_kembali' => '2026-09-28 08:20:00',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sudah Kembali');
    }

    /**
     * Test 8: Sebelum sesi ditampilkan 'Akan Bertugas'
     */
    public function test_8_sebelum_sesi_ditampilkan_akan_bertugas(): void
    {
        // Jam 06:40 (sebelum sesi 07:00 - 09:30)
        Carbon::setTestNow(Carbon::parse('2026-09-28 06:40:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Pagi');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Guru Piket Berikutnya');
        $response->assertSee('Akan Datang');
        $response->assertSee('Akan Bertugas');
    }

    /**
     * Test 9: Setelah sesi ditampilkan 'Sesi Selesai'
     */
    public function test_9_setelah_sesi_ditampilkan_sesi_selesai(): void
    {
        // Jam 10:00 (setelah sesi 07:00 - 09:30 selesai)
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Selesai Sesi');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        // Karena tidak ada sesi berikutnya pada hari itu
        $response->assertSee('Tidak ada sesi Guru Piket berikutnya hari ini.');
    }

    /**
     * Test 10: Conflict replacement tidak menampilkan petugas hasil pilihan otomatis
     */
    public function test_10_conflict_replacement_tidak_menampilkan_petugas_hasil_pilihan_otomatis(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userAsal, $guruAsal] = $this->createGuruUser('Guru Asal Conflict');
        [$userP1, $guruP1] = $this->createGuruUser('Pengganti Satu');
        [$userP2, $guruP2] = $this->createGuruUser('Pengganti Dua');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruAsal->id]);

        // Anomali: Ada 2 replacement aktif sekaligus untuk guru asal yang sama
        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruP1->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruAsal->id,
            'guru_pengganti_id' => $guruP2->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $response = $this->actingAs($userAsal)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        // Harus menampilkan pesan aman dan tidak memilih salah satu pengganti secara otomatis
        $response->assertSee('Data jadwal membutuhkan pemeriksaan admin.');
        $response->assertDontSee('Pengganti Satu');
        $response->assertDontSee('Pengganti Dua');
    }

    /**
     * Test 11: Guru tidak dapat mengubah jadwal resmi melalui dashboard
     */
    public function test_11_guru_tidak_dapat_mengubah_jadwal_resmi_melalui_dashboard(): void
    {
        [$userA, $guruA] = $this->createGuruUser('Guru Test Mutation');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // Dashboard adalah read-only (hanya GET)
        $response = $this->actingAs($userA)->get(route('guru.dashboard'));
        $response->assertStatus(200);

        // Percobaan POST/PUT ke dashboard tidak diizinkan / method not allowed
        $postResponse = $this->actingAs($userA)->post(route('guru.dashboard'), [
            'jam_mulai' => '10:00:00',
        ]);
        $this->assertEquals(405, $postResponse->getStatusCode());

        // Jadwal resmi tetap tidak berubah
        $this->assertEquals('07:00:00', $jadwal->fresh()->jam_mulai);
    }

    /**
     * Test 12: Informasi sensitif checklog tidak muncul pada dashboard Guru
     */
    public function test_12_informasi_sensitif_checklog_tidak_muncul_pada_dashboard_guru(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:15:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuruUser('Guru Privasi Checklog');

        $jadwal = JadwalPiket::create([
            'hari' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-07-19',
            'is_active' => true,
        ]);

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        $alasanRahasia = 'Rahasia Pribadi: Mengurus warisan keluarga';
        $tujuanKhusus = 'Kantor Notaris Sudirman';
        $lokasiKhusus = 'Jl. Protokol Nomor 99';

        GuruChecklog::create([
            'guru_id' => $guruA->id,
            'alasan' => $alasanRahasia,
            'tujuan' => $tujuanKhusus,
            'lokasi' => $lokasiKhusus,
            'jam_keluar' => '2026-09-28 08:00:00',
            'status' => 'keluar',
        ]);

        $response = $this->actingAs($userA)->get(route('guru.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Sedang Keluar');

        // Pastikan alasan, tujuan, dan lokasi rahasia tidak bocor di dashboard
        $response->assertDontSee($alasanRahasia);
        $response->assertDontSee($tujuanKhusus);
        $response->assertDontSee($lokasiKhusus);
    }
}
