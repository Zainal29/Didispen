<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\DispensasiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DispensasiPaket1ToleransiDanTerlambatTest extends TestCase
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
            'name'     => 'Ahmad Zaki',
            'email'    => 'zaki@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $siswaUser->id,
            'nama_lengkap' => 'Ahmad Zaki',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
        ]);
    }

    /**
     * 1. Dispensasi berstatus disetujui otomatis kadaluarsa jika lewat 20 menit tidak scan keluar di pos satpam.
     */
    public function test_dispensasi_disetujui_otomatis_kadaluarsa_jika_lewat_20_menit_belum_scan_keluar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'     => $this->siswa->id,
            'nomor_surat'  => Dispensasi::generateNomorSurat(),
            'kategori'     => 'izin',
            'alasan'       => 'Keperluan keluarga',
            'tujuan'       => 'Rumah',
            'tanggal_izin' => '2026-10-22',
            'jam_keluar'   => '09:00',
            'jam_kembali'  => '11:00',
            'status'       => 'disetujui',
            'approved_at'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'qr_token'     => 'token_test_20m_1',
        ]);

        // Pada menit ke-15 (09:15 WIB), pengajuan belum kadaluarsa
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:15:00', 'Asia/Jakarta'));
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);

        // Pada menit ke-21 (09:21 WIB), batas 20 menit terlewati -> harus kadaluarsa
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:21:00', 'Asia/Jakarta'));
        $affected = DispensasiService::autoExpireOverdueDispensasi();
        $this->assertGreaterThanOrEqual(1, $affected);

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
        $this->assertStringContainsString('20 menit', $dispensasi->catatan_admin);
        $this->assertEquals('Kadaluarsa (Tidak Scan Keluar)', $dispensasi->status_badge['text']);
    }

    /**
     * 2. Satpam Scan Controller menolak QR code dengan 422 jika lewat 20 menit sejak disetujui.
     */
    public function test_satpam_scan_menolak_qr_jika_lewat_batas_scan_keluar_20_menit(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'     => $this->siswa->id,
            'nomor_surat'  => Dispensasi::generateNomorSurat(),
            'kategori'     => 'izin',
            'alasan'       => 'Beli perlengkapan lomba',
            'tujuan'       => 'Toko Buku',
            'tanggal_izin' => '2026-10-22',
            'jam_keluar'   => '09:00',
            'jam_kembali'  => '11:00',
            'status'       => 'disetujui',
            'approved_at'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'qr_token'     => 'token_scan_satpam_20m',
        ]);

        // Majukan waktu ke 09:25 WIB (25 menit setelah di-ACC)
        Carbon::setTestNow(Carbon::parse('2026-10-22 09:25:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->satpam)
            ->postJson(route('satpam.scan.verify'), [
                'qr_data' => 'token_scan_satpam_20m',
                'action'  => 'check',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('KADALUARSA', $response->json('message'));

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
    }

    /**
     * 3. Siswa yang keluar tapi tidak scan kembali mendapatkan badge Kadaluarsa (Tidak Scan Kembali) dengan border merah tegas.
     */
    public function test_status_badge_kadaluarsa_tidak_scan_kembali_dan_border_merah(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => Dispensasi::generateNomorSurat(),
            'kategori'             => 'izin',
            'alasan'               => 'Fotokopi modul',
            'tujuan'               => 'Fotokopi depan',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '09:00',
            'jam_kembali'          => '09:45',
            'status'               => 'kadaluarsa',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 09:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => null,
            'catatan_admin'        => 'Kadaluarsa otomatis: Siswa tidak melakukan scan kembali hingga KBM berakhir (Tidak Kembali).',
        ]);

        $badge = $dispensasi->status_badge;
        $this->assertEquals('Kadaluarsa (Tidak Scan Kembali)', $badge['text']);
        $this->assertStringContainsString('border-2 border-rose-500', $badge['class']);
        $this->assertStringContainsString('bg-rose-50', $badge['class']);
        $this->assertStringContainsString('text-rose-700', $badge['class']);
        $this->assertStringContainsString('font-bold', $badge['class']);
        $this->assertTrue($badge['is_late']);
        $this->assertTrue($dispensasi->isNotReturned());
        $this->assertTrue($dispensasi->isTerlambat());
    }

    /**
     * 4. Scope scopeTerlambat menyaring 3 kriteria keterlambatan secara seragam.
     */
    public function test_scope_terlambat_menyaring_tiga_kriteria_dengan_benar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-22 10:00:00', 'Asia/Jakarta'));

        // a. Selesai Tepat Waktu (Bukan terlambat)
        $tepatWaktu = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => Dispensasi::generateNomorSurat(),
            'kategori'             => 'izin',
            'alasan'               => 'Tepat waktu',
            'tujuan'               => 'Urusan',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:00',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'status'               => 'selesai',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => Carbon::parse('2026-10-22 08:55:00', 'Asia/Jakarta'),
        ]);

        // b. Selesai Terlambat (> 5 menit toleransi, kembali jam 09:10 saat batas 09:00)
        $selesaiTerlambat = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => Dispensasi::generateNomorSurat(),
            'kategori'             => 'izin',
            'alasan'               => 'Selesai telat',
            'tujuan'               => 'Urusan',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:00',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'status'               => 'selesai',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => Carbon::parse('2026-10-22 09:10:00', 'Asia/Jakarta'),
        ]);

        // c. Sedang Keluar & Melewati Batas Waktu (Overdue)
        $sedangOverdue = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => Dispensasi::generateNomorSurat(),
            'kategori'             => 'izin',
            'alasan'               => 'Sedang telat',
            'tujuan'               => 'Urusan',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:30',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:30:00', 'Asia/Jakarta'),
            'status'               => 'keluar',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => null,
        ]);

        // d. Kadaluarsa (Keluar tapi tidak pernah scan kembali)
        $tidakKembali = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => Dispensasi::generateNomorSurat(),
            'kategori'             => 'izin',
            'alasan'               => 'Tidak pernah kembali',
            'tujuan'               => 'Urusan',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:00',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'status'               => 'kadaluarsa',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => null,
        ]);

        // Uji helper isTerlambat()
        $this->assertFalse($tepatWaktu->isTerlambat());
        $this->assertTrue($selesaiTerlambat->isTerlambat());
        $this->assertTrue($sedangOverdue->isTerlambat());
        $this->assertTrue($tidakKembali->isTerlambat());

        // Uji query scopeTerlambat()
        $terlambatList = Dispensasi::terlambat()->pluck('id')->toArray();
        $this->assertNotContains($tepatWaktu->id, $terlambatList);
        $this->assertContains($selesaiTerlambat->id, $terlambatList);
        $this->assertContains($sedangOverdue->id, $terlambatList);
        $this->assertContains($tidakKembali->id, $terlambatList);
        $this->assertCount(3, $terlambatList);
    }
}
