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

class DispensasiPaket2FilterTerlambatDanBannerTest extends TestCase
{
    use RefreshDatabase;

    private User $userSiswa;
    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 1', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        $this->userSiswa = User::create([
            'name'     => 'Fajar Pratama',
            'email'    => 'fajar@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->userSiswa->id,
            'nama_lengkap' => 'Fajar Pratama',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
        ]);
    }

    /**
     * 1. Dashboard menampilkan tombol kembar Tampilkan Semua Pengajuan & Pengajuan Terlambat
     */
    public function test_dashboard_siswa_menampilkan_tombol_kembar_dengan_hitungan_terlambat(): void
    {
        // Kondisi 1: Belum ada pengajuan (0 keterlambatan)
        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertSee('Tampilkan Semua Pengajuan (0)');
        $response->assertSee('Riwayat Terlambat (0)');

        // Kondisi 2: Tambahkan 1 pengajuan terlambat
        Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/LATE01',
            'kategori'             => 'izin',
            'alasan'               => 'Urusan keluarga',
            'tujuan'               => 'Rumah',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:00',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'status'               => 'selesai',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => Carbon::parse('2026-10-22 09:15:00', 'Asia/Jakarta'), // Terlambat 15 menit
        ]);

        $responseAfter = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $responseAfter->assertOk();
        $responseAfter->assertSee('Tampilkan Semua Pengajuan (1)');
        $responseAfter->assertSee('Pengajuan Terlambat (1)');
    }

    /**
     * 2. Filter status=terlambat di riwayat pengajuan hanya menampilkan pengajuan terlambat
     */
    public function test_filter_pengajuan_terlambat_menyaring_hanya_keterlambatan(): void
    {
        // Pengajuan A: Tepat waktu
        $tepat = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/ONTIME99',
            'kategori'             => 'izin',
            'alasan'               => 'Tepat waktu',
            'tujuan'               => 'Perpus',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:00',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'status'               => 'selesai',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => Carbon::parse('2026-10-22 08:50:00', 'Asia/Jakarta'),
        ]);

        // Pengajuan B: Terlambat kembali
        $terlambat = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/LATE99',
            'kategori'             => 'izin',
            'alasan'               => 'Terlambat kembali',
            'tujuan'               => 'Fotokopi',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '09:00',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 09:00:00', 'Asia/Jakarta'),
            'status'               => 'selesai',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => Carbon::parse('2026-10-22 09:20:00', 'Asia/Jakarta'),
        ]);

        // Request Semua Pengajuan
        $resAll = $this->actingAs($this->userSiswa)->get(route('siswa.pengajuan.index'));
        $resAll->assertOk();
        $resAll->assertSee('DISP/TEST/ONTIME99');
        $resAll->assertSee('DISP/TEST/LATE99');

        // Request Filter Terlambat
        $resLate = $this->actingAs($this->userSiswa)->get(route('siswa.pengajuan.index', ['status' => 'terlambat']));
        $resLate->assertOk();
        $resLate->assertSee('DISP/TEST/LATE99');
        $resLate->assertDontSee('DISP/TEST/ONTIME99');
    }

    /**
     * 3. Banner edukasi muncul ketika siswa pernah terlambat (total >= 1)
     */
    public function test_banner_edukasi_muncul_jika_pernah_terlambat(): void
    {
        Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/LATE_BANNER',
            'kategori'             => 'izin',
            'alasan'               => 'Beli modul',
            'tujuan'               => 'Depan',
            'tanggal_izin'         => '2026-10-22',
            'jam_keluar'           => '08:00',
            'jam_kembali'          => '08:30',
            'batas_waktu_kembali'  => Carbon::parse('2026-10-22 08:30:00', 'Asia/Jakarta'),
            'status'               => 'kadaluarsa',
            'waktu_keluar_aktual'  => Carbon::parse('2026-10-22 08:05:00', 'Asia/Jakarta'),
            'waktu_kembali_aktual' => null, // Tidak pernah scan kembali
        ]);

        $response = $this->actingAs($this->userSiswa)->get(route('siswa.pengajuan.index'));
        $response->assertOk();
        $response->assertSee('Catatan Evaluasi Kedisiplinan');
        $response->assertSee('Tercatat Terlambat: 1 Kali');
        $response->assertSee('Perkirakan Waktu dengan Matang');
        $response->assertSee('Wajib Scan Kembali di Pos Satpam');
        $response->assertSee('Unggah Foto Bukti Kegiatan');
    }

    /**
     * 4. Banner apresiasi muncul ketika siswa belum pernah terlambat (0 kali)
     */
    public function test_banner_apresiasi_muncul_jika_tidak_pernah_terlambat(): void
    {
        $response = $this->actingAs($this->userSiswa)->get(route('siswa.pengajuan.index'));
        $response->assertOk();
        $response->assertSee('Luar Biasa! Kedisiplinan Sangat Baik');
        $response->assertSee('0 Keterlambatan');
        $response->assertSee('Pertahankan kedisiplinan dan tanggung jawab Anda');
    }
}
