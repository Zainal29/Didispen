<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PertukaranJadwalPiket;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SatpamGuruPiketTest extends TestCase
{
    use RefreshDatabase;

    protected User $satpamUser;
    protected Kelas $kelas;
    protected Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kode_jurusan' => 'TKJ',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XI TKJ 1',
            'tingkat' => 'XI',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $unique = rand(100000, 999999);
        $this->satpamUser = User::create([
            'name' => 'Satpam Pos 1',
            'email' => "satpam_{$unique}@sch.id",
            'role' => 'satpam',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createGuru(string $name, array $attributes = []): array
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
        ], $attributes));

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

    private function createDispensasi(Guru $guru, string $status = 'disetujui'): Dispensasi
    {
        $unique = rand(100000, 999999);
        $siswaUser = User::create([
            'name' => 'Siswa ' . $unique,
            'email' => "siswa_{$unique}@sch.id",
            'role' => 'siswa',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nama_lengkap' => 'Siswa ' . $unique,
            'nis_nip' => (string) $unique,
            'kelas_id' => $this->kelas->id,
            'jurusan_id' => $this->jurusan->id,
            'status_aktif' => true,
        ]);

        return Dispensasi::create([
            'nomor_surat' => 'DISP/' . rand(1000, 9999),
            'siswa_id' => $siswa->id,
            'guru_id' => $guru->id,
            'kategori' => 'keperluan_keluarga',
            'alasan' => 'Keperluan keluarga penting',
            'tujuan' => 'Rumah',
            'jam_keluar' => '08:00',
            'jam_kembali' => '10:00',
            'status' => $status,
            'created_at' => now(),
        ]);
    }

    /**
     * Test 1: Dashboard Satpam dapat melihat Guru Piket
     */
    public function test_1_dashboard_satpam_dapat_melihat_guru_piket(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Bapak Lutfianto');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Piket Hari Ini');
        $response->assertSee('Bapak Lutfianto');
    }

    /**
     * Test 2: Sesi aktif (Sedang Berlangsung)
     */
    public function test_2_sesi_aktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:30:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Ibu Diana');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Berlangsung');
        $response->assertSee('07:00 – 09:30');
    }

    /**
     * Test 3: Sesi berikutnya (Akan Datang)
     */
    public function test_3_sesi_berikutnya(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Bapak Ahmad');
        $jadwal = $this->createJadwal(1, '09:30:00', '12:00:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Akan Datang');
        $response->assertSee('09:30 – 12:00');
    }

    /**
     * Test 4: Semua sesi hari ini selesai (Empty State)
     */
    public function test_4_semua_sesi_selesai(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 14:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Bapak Ahmad');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Tidak ada sesi Guru Piket berikutnya hari ini.');
    }

    /**
     * Test 5: Replacement aktif (Guru B menggantikan Guru A)
     */
    public function test_5_replacement_aktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuru('Guru Asli Satpam A');
        [$userB, $guruB] = $this->createGuru('Guru Pengganti Satpam B');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'disetujui',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Pengganti Satpam B');
        $response->assertSee('Menggantikan: Guru Asli Satpam A');
    }

    /**
     * Test 6: Replacement nonaktif (is_active = false tetap tampilkan guru resmi)
     */
    public function test_6_replacement_nonaktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuru('Guru Tetap Satpam A');
        [$userB, $guruB] = $this->createGuru('Guru Ditolak Satpam B');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'ditolak',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Tetap Satpam A');
        $response->assertDontSee('Menggantikan');
    }

    /**
     * Test 7: Guru sedang keluar
     */
    public function test_7_guru_sedang_keluar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Keluar Pos');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 07:30:00',
            'status' => 'keluar',
            'alasan' => 'Urusan Dinas Luar',
            'tujuan' => 'Kantor Pemda',
        ]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Keluar');
    }

    /**
     * Test 8: Guru sudah kembali
     */
    public function test_8_guru_sudah_kembali(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:30:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Kembali Pos');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 07:15:00',
            'jam_kembali' => '2026-09-28 08:00:00',
            'status' => 'selesai',
            'alasan' => 'Dinas',
            'tujuan' => 'Bank',
        ]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Sudah Kembali');
    }

    /**
     * Test 9: Checklog di luar sesi tidak memengaruhi status sesi saat ini
     */
    public function test_9_checklog_di_luar_sesi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Sesi Sekarang');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        // Checklog selesai terjadi jam 05:00 - 06:00
        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 05:00:00',
            'jam_kembali' => '2026-09-28 06:00:00',
            'status' => 'selesai',
            'alasan' => 'Pagi',
            'tujuan' => 'Toko',
        ]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Bertugas');
        $infoPiket = $response->viewData('infoPiket');
        $this->assertEquals('Sedang Bertugas', $infoPiket['petugas'][0]['status']);
        $this->assertNotEquals('Sudah Kembali', $infoPiket['petugas'][0]['status']);
    }

    /**
     * Test 10: Conflict replacement menghasilkan pesan aman
     */
    public function test_10_conflict(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuru('Guru Konflik Satpam');
        [$userB, $guruB] = $this->createGuru('Guru Pengganti B1');
        [$userC, $guruC] = $this->createGuru('Guru Pengganti C1');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // Dua replacement aktif sekaligus untuk slot yang sama
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

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee('Data Guru Piket sedang diperiksa.');
        $response->assertDontSee('Guru Pengganti B1');
        $response->assertDontSee('Guru Pengganti C1');
    }

    /**
     * Test 11: Data sensitif checklog tidak bocor di dashboard Satpam
     */
    public function test_11_data_sensitif_tidak_bocor(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Rahasia');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 07:30:00',
            'status' => 'keluar',
            'alasan' => 'Urusan Pribadi Sangat Rahasia Sekali',
            'tujuan' => 'Klinik Privat Tertutup',
            'lokasi' => 'Jl. Rahasia No 777',
        ]);

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Urusan Pribadi Sangat Rahasia Sekali');
        $response->assertDontSee('Klinik Privat Tertutup');
        $response->assertDontSee('Jl. Rahasia No 777');
    }

    /**
     * Test 12: Satpam tetap dapat menggunakan flow dispensasi
     */
    public function test_12_satpam_tetap_dapat_menggunakan_flow_dispensasi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Pembina');
        $dispensasi = $this->createDispensasi($guru, 'disetujui');

        $response = $this->actingAs($this->satpamUser)->get(route('satpam.dashboard'));

        $response->assertOk();
        $response->assertSee($dispensasi->nomor_surat);
        $response->assertSee('Menunggu');
    }

    /**
     * Test 13: Foto dispensasi tetap aman dan flow konfirmasi berjalan normal
     */
    public function test_13_foto_dispensasi_tetap_aman(): void
    {
        [$userGuru, $guru] = $this->createGuru('Guru Izin');
        $dispensasi = $this->createDispensasi($guru, 'keluar');
        $dispensasi->update([
            'foto_verifikasi' => 'verifikasi/foto_123.jpg',
            'foto_bukti' => 'bukti/foto_bukti_123.jpg',
        ]);

        // Satpam konfirmasi kembali
        $response = $this->actingAs($this->satpamUser)->post(route('satpam.konfirmasi.kembali', $dispensasi->id));

        $response->assertSessionHas('success');
        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertEquals('verifikasi/foto_123.jpg', $dispensasi->foto_verifikasi);
        $this->assertEquals('bukti/foto_bukti_123.jpg', $dispensasi->foto_bukti);
    }

    /**
     * Test 14: Dashboard Guru tetap normal
     */
    public function test_14_dashboard_guru_tetap_normal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Standar');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($userGuru)->get(route('guru.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Piket Hari Ini');
        $response->assertSee('Tukar Jadwal');
    }
}
