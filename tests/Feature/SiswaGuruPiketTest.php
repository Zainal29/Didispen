<?php

namespace Tests\Feature;

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

class SiswaGuruPiketTest extends TestCase
{
    use RefreshDatabase;

    protected User $siswaUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kode_jurusan' => 'RPL',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $unique = rand(100000, 999999);
        $this->siswaUser = User::create([
            'name' => 'Siswa Penguji ' . $unique,
            'email' => "siswa_{$unique}@sch.id",
            'role' => 'siswa',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_lengkap' => 'Siswa Penguji ' . $unique,
            'nis_nip' => (string) $unique,
            'kelas_id' => $this->kelas->id,
            'jurusan_id' => $this->jurusan->id,
            'status_aktif' => true,
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

    /**
     * Test 1: Siswa dapat melihat Guru Piket di Dashboard
     */
    public function test_1_siswa_dapat_melihat_guru_piket(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Bapak Lutfianto');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Piket Hari Ini');
        $response->assertSee('Bapak Lutfianto');
    }

    /**
     * Test 2: Siswa melihat sesi aktif (Sedang Berlangsung)
     */
    public function test_2_siswa_melihat_sesi_aktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:30:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Ibu Diana');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Berlangsung');
        $response->assertSee('07:00 – 09:30');
    }

    /**
     * Test 3: Siswa melihat sesi berikutnya (Akan Datang)
     */
    public function test_3_siswa_melihat_sesi_berikutnya(): void
    {
        // 08:00 WIB, belum memasuki sesi 09:30 - 12:00
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Bapak Ahmad');
        $jadwal = $this->createJadwal(1, '09:30:00', '12:00:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Akan Datang');
        $response->assertSee('09:30 – 12:00');
    }

    /**
     * Test 4: Setelah semua sesi selesai
     */
    public function test_4_setelah_semua_sesi(): void
    {
        // 14:00 WIB, sesi berakhir pukul 09:30
        Carbon::setTestNow(Carbon::parse('2026-09-28 14:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Bapak Ahmad');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Tidak ada sesi berikutnya hari ini.');
    }

    /**
     * Test 5: Replacement aktif (Guru B menggantikan Guru A)
     */
    public function test_5_replacement_aktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuru('Guru Asli A');
        [$userB, $guruB] = $this->createGuru('Guru Pengganti B');

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

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Pengganti B');
        $response->assertSee('Menggantikan: Guru Asli A');
    }

    /**
     * Test 6: Replacement nonaktif (is_active = false tetap tampilkan guru resmi)
     */
    public function test_6_replacement_nonaktif(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuru('Guru Tetap A');
        [$userB, $guruB] = $this->createGuru('Guru Batal B');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        PertukaranJadwalPiket::create([
            'jadwal_piket_id' => $jadwal->id,
            'guru_asal_id' => $guruA->id,
            'guru_pengganti_id' => $guruB->id,
            'tanggal' => '2026-09-28',
            'status' => 'dibatalkan',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Guru Tetap A');
        $response->assertDontSee('Menggantikan');
    }

    /**
     * Test 7: Guru sedang keluar
     */
    public function test_7_guru_sedang_keluar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Keluar Sesaat');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 07:30:00',
            'status' => 'keluar',
            'alasan' => 'Urusan Bank',
            'tujuan' => 'Bank Mandiri',
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Keluar');
    }

    /**
     * Test 8: Guru sudah kembali
     */
    public function test_8_guru_sudah_kembali(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:30:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Kembali');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 07:15:00',
            'jam_kembali' => '2026-09-28 08:00:00',
            'status' => 'selesai',
            'alasan' => 'Dinas',
            'tujuan' => 'Kantor Dinas',
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Sudah Kembali');
    }

    /**
     * Test 9: Checklog selesai di luar sesi tidak memengaruhi status sesi sekarang
     */
    public function test_9_checklog_di_luar_sesi(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Pagi');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        // Checklog selesai terjadi jam 05:00 - 06:00 (jauh sebelum sesi 07:00)
        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 05:00:00',
            'jam_kembali' => '2026-09-28 06:00:00',
            'status' => 'selesai',
            'alasan' => 'Pagi',
            'tujuan' => 'Pasar',
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Bertugas');
        $response->assertDontSee('Sudah Kembali');
    }

    /**
     * Test 10: Data sensitif checklog tidak bocor ke dashboard siswa
     */
    public function test_10_data_sensitif_tidak_bocor(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userGuru, $guru] = $this->createGuru('Guru Sensitif');
        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guru->id]);

        GuruChecklog::create([
            'guru_id' => $guru->id,
            'jam_keluar' => '2026-09-28 07:30:00',
            'status' => 'keluar',
            'alasan' => 'Urusan Medis Sangat Rahasia',
            'tujuan' => 'Klinik Spesialis Tertentu',
            'lokasi' => 'Jl. Pribadi No 99',
        ]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Urusan Medis Sangat Rahasia');
        $response->assertDontSee('Klinik Spesialis Tertentu');
        $response->assertDontSee('Jl. Pribadi No 99');
    }

    /**
     * Test 11: Conflict replacement menghasilkan pesan aman
     */
    public function test_11_conflict_replacement_aman(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        [$userA, $guruA] = $this->createGuru('Guru Konflik A');
        [$userB, $guruB] = $this->createGuru('Guru Pengganti B');
        [$userC, $guruC] = $this->createGuru('Guru Pengganti C');

        $jadwal = $this->createJadwal(1, '07:00:00', '09:30:00');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwal->id, 'guru_id' => $guruA->id]);

        // Dua replacement aktif sekaligus untuk slot yang sama (conflict)
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

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Data Guru Piket sedang diperiksa.');
        $response->assertDontSee('Guru Pengganti B');
        $response->assertDontSee('Guru Pengganti C');
    }

    /**
     * Test 12: Siswa tidak dapat melakukan mutasi data piket (403/404)
     */
    public function test_12_siswa_tidak_dapat_mengubah_data_piket(): void
    {
        // Siswa mencoba membuat swap
        $resStore = $this->actingAs($this->siswaUser)->post(route('guru.piket.swap.store'), [
            'jadwal_piket_id' => 1,
            'guru_pengganti_id' => 2,
            'tanggal' => '2026-09-28',
        ]);
        $this->assertTrue(in_array($resStore->getStatusCode(), [403, 404]));

        // Siswa mencoba accept
        $resAccept = $this->actingAs($this->siswaUser)->post(route('guru.piket.swap.accept', 1));
        $this->assertTrue(in_array($resAccept->getStatusCode(), [403, 404]));

        // Siswa mencoba reject
        $resReject = $this->actingAs($this->siswaUser)->post(route('guru.piket.swap.reject', 1));
        $this->assertTrue(in_array($resReject->getStatusCode(), [403, 404]));

        // Siswa mencoba cancel
        $resCancel = $this->actingAs($this->siswaUser)->post(route('guru.piket.swap.cancel', 1));
        $this->assertTrue(in_array($resCancel->getStatusCode(), [403, 404]));
    }

    /**
     * Test 13: Dashboard Guru tetap normal
     */
    public function test_13_guru_tetap_normal(): void
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

    /**
     * Test 14: Fitur dispensasi siswa existing tetap normal
     */
    public function test_14_regression_existing_dashboard_siswa(): void
    {
        $response = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Halo, ' . $this->siswaUser->name);
        $response->assertSee('Buat Pengajuan');
        $response->assertSee('Pengajuan Terbaru');
    }
}
