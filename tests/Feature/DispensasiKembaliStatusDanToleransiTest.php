<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DispensasiKembaliStatusDanToleransiTest extends TestCase
{
    use RefreshDatabase;

    private User $satpam;
    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->satpam = User::create([
            'name'     => 'Satpam Gerbang',
            'email'    => 'satpam_gerbang@sch.id',
            'role'     => 'satpam',
            'password' => Hash::make('password'),
        ]);

        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 2', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);
        $siswaUser = User::create([
            'name'     => 'Sabrian Nuh',
            'email'    => 'sabrian@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);
        $this->siswa = Siswa::create([
            'user_id'      => $siswaUser->id,
            'nama_lengkap' => 'Muhammad Sabrian Nuh',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
        ]);
    }

    public function test_kembali_lebih_awal_dari_batas_waktu_mendapatkan_status_kembali_lebih_awal(): void
    {
        $batasKembali = Carbon::parse('2026-10-19 13:23:00', 'Asia/Jakarta');
        $waktuAktual = Carbon::parse('2026-10-19 11:35:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/001',
            'kategori'             => 'izin',
            'alasan'               => 'Urusan keluarga',
            'tujuan'               => 'Rumah',
            'jam_keluar'           => '11:23',
            'jam_kembali'          => '13:23',
            'batas_waktu_kembali'  => $batasKembali,
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-19 11:23:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => $waktuAktual,
            'status'               => 'selesai',
        ]);

        $this->assertTrue($dispensasi->isReturnedEarly());
        $this->assertFalse($dispensasi->isReturnedLate());
        $badge = $dispensasi->status_badge;
        $this->assertEquals('Selesai (Kembali Lebih Awal)', $badge['text']);
        $this->assertFalse($badge['is_late']);
    }

    public function test_kembali_dalam_toleransi_5_menit_tetap_tercatat_terlambat_dengan_catatan_toleransi(): void
    {
        $batasKembali = Carbon::parse('2026-10-19 13:00:00', 'Asia/Jakarta');
        // Kembali 3 menit setelah batas (dalam masa toleransi 5 menit gerbang)
        $waktuAktual = Carbon::parse('2026-10-19 13:03:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/002',
            'kategori'             => 'izin',
            'alasan'               => 'Urusan',
            'tujuan'               => 'Dinas',
            'jam_keluar'           => '11:00',
            'jam_kembali'          => '13:00',
            'batas_waktu_kembali'  => $batasKembali,
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-19 11:00:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => $waktuAktual,
            'status'               => 'selesai',
        ]);

        $this->assertFalse($dispensasi->isReturnedEarly());
        $this->assertTrue($dispensasi->isReturnedLate());
        $this->assertTrue($dispensasi->isWithinTolerance());
        $badge = $dispensasi->status_badge;
        $this->assertEquals('Selesai (Terlambat 3m)', $badge['text']);
        $this->assertTrue($badge['is_late']);
        $this->assertTrue($badge['within_tolerance']);
        $this->assertStringContainsString('toleransi gerbang', $dispensasi->getKeteranganToleransiKembaliText());
    }

    public function test_kembali_melewati_toleransi_5_menit_mendapatkan_status_terlambat(): void
    {
        $batasKembali = Carbon::parse('2026-10-19 13:00:00', 'Asia/Jakarta');
        // Kembali 10 menit setelah batas (lewat dari toleransi 5 menit)
        $waktuAktual = Carbon::parse('2026-10-19 13:10:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/003',
            'kategori'             => 'izin',
            'alasan'               => 'Urusan',
            'tujuan'               => 'Bank',
            'jam_keluar'           => '11:00',
            'jam_kembali'          => '13:00',
            'batas_waktu_kembali'  => $batasKembali,
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-19 11:00:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => $waktuAktual,
            'status'               => 'selesai',
        ]);

        $this->assertFalse($dispensasi->isReturnedEarly());
        $this->assertTrue($dispensasi->isReturnedLate());
        $badge = $dispensasi->status_badge;
        $this->assertStringContainsString('Selesai (Terlambat', $badge['text']);
        $this->assertTrue($badge['is_late']);
    }

    public function test_scan_satpam_kembali_lebih_awal_menghasilkan_pesan_lebih_awal(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-19 11:35:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/004',
            'kategori'             => 'izin',
            'alasan'               => 'Urusan',
            'tujuan'               => 'Fotokopi',
            'jam_keluar'           => '11:23',
            'jam_kembali'          => '13:23',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-19 13:23:00', 'Asia/Jakarta'),
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-19 11:23:00', 'Asia/Jakarta'),
            'status'               => 'keluar',
            'qr_token'             => str_repeat('z', 64),
        ]);

        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $dispensasi->qr_token]),
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Siswa berhasil dicatat KEMBALI (Lebih Awal).',
                'is_terlambat' => false,
            ]);

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertTrue($dispensasi->isReturnedEarly());
        $this->assertEquals('Selesai (Kembali Lebih Awal)', $dispensasi->status_badge['text']);
    }

    public function test_grace_period_dan_resmi_terlambat_pada_rentang_toleransi_5_menit(): void
    {
        $batasKembali = Carbon::parse('2026-10-26 09:20:00', 'Asia/Jakarta');
        $waktuKeluar = Carbon::parse('2026-10-26 09:00:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/GRACE01',
            'kategori'             => 'izin',
            'alasan'               => 'Fotokopi modul',
            'tujuan'               => 'Depan sekolah',
            'jam_keluar'           => '09:00',
            'jam_kembali'          => '09:20',
            'batas_waktu_kembali'  => $batasKembali,
            'waktu_keluar_aktual'  => $waktuKeluar,
            'status'               => 'keluar',
            'qr_token'             => str_repeat('g', 64),
        ]);

        // 1. Pukul 09:01 (Masih masa izin normal)
        Carbon::setTestNow(Carbon::parse('2026-10-26 09:01:00', 'Asia/Jakarta'));
        $this->assertFalse($dispensasi->isCurrentlyInGracePeriod());
        $this->assertFalse($dispensasi->isOfficiallyLate());

        // 2. Pukul 09:21 (Masuk masa toleransi antrean gerbang 5 menit)
        Carbon::setTestNow(Carbon::parse('2026-10-26 09:21:00', 'Asia/Jakarta'));
        $this->assertTrue($dispensasi->isCurrentlyInGracePeriod());
        $this->assertFalse($dispensasi->isOfficiallyLate());
        $this->assertEquals(4, $dispensasi->getSisaMenitToleransi());
        $this->assertEquals('09:25 WIB', $dispensasi->batas_toleransi_formatted);

        // Dashboard siswa pada 09:21 menampilkan banner toleransi, BUKAN terlambat resmi
        $responseDashboard = $this->actingAs($this->siswa->user)->get(route('siswa.dashboard'));
        $responseDashboard->assertOk();
        $responseDashboard->assertSee('Masa Toleransi Antrean');
        $responseDashboard->assertSee('09:25 WIB');
        $responseDashboard->assertDontSee('Peringatan: Anda Resmi Dinyatakan Terlambat!');

        // 3. Pukul 09:26 (Melewati batas toleransi 09:25 -> Resmi dinyatakan terlambat)
        Carbon::setTestNow(Carbon::parse('2026-10-26 09:26:00', 'Asia/Jakarta'));
        $this->assertFalse($dispensasi->isCurrentlyInGracePeriod());
        $this->assertTrue($dispensasi->isOfficiallyLate());

        // Dashboard siswa pada 09:26 menampilkan banner resmi terlambat
        $responseDashboardLate = $this->actingAs($this->siswa->user)->get(route('siswa.dashboard'));
        $responseDashboardLate->assertOk();
        $responseDashboardLate->assertSee('Peringatan: Anda Resmi Dinyatakan Terlambat!');
        $responseDashboardLate->assertSee('Toleransi Habis');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
