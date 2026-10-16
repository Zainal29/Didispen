<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\PertukaranJadwalPiket;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruPiketOtorisasiTest extends TestCase
{
    use RefreshDatabase;

    private User $guruPiketUser1;
    private Guru $guruPiket1;
    private User $guruPiketUser2;
    private Guru $guruPiket2;
    private User $guruBiasaUser;
    private Guru $guruBiasa;
    private Siswa $siswa;
    private JadwalPiket $jadwalSesi1;
    private JadwalPiket $jadwalSesi2;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Hari Jumat (ISO 5), 2026-10-16
        Carbon::setTestNow(Carbon::parse('2026-10-16 08:00:00', 'Asia/Jakarta'));

        $jurusan = Jurusan::create([
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kode_jurusan' => 'TKJ',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XII TKJ 1',
            'tingkat' => 'XII',
            'jurusan_id' => $jurusan->id,
        ]);

        // Siswa
        $siswaUser = User::create([
            'name'     => 'Siswa Ahmad',
            'email'    => 'ahmad@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '999001',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $siswaUser->id,
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
            'nama_lengkap' => 'Ahmad Dahlan',
            'nis_nip'      => '999001',
            'status_aktif' => true,
        ]);

        // Guru Piket Sesi 1 (07:00 - 09:30)
        $this->guruPiketUser1 = User::create([
            'name'     => 'Guru Sesi Satu',
            'email'    => 'sesi1@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '19810101',
            'password' => Hash::make('password'),
        ]);
        $this->guruPiket1 = Guru::create([
            'user_id'        => $this->guruPiketUser1->id,
            'nip'            => '19810101',
            'nama_lengkap'   => 'Guru Sesi Satu, S.Pd.',
            'mata_pelajaran' => 'Matematika',
            'status_aktif'   => true,
        ]);

        // Guru Piket Sesi 2 (09:30 - 14:00)
        $this->guruPiketUser2 = User::create([
            'name'     => 'Guru Sesi Dua',
            'email'    => 'sesi2@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '19820202',
            'password' => Hash::make('password'),
        ]);
        $this->guruPiket2 = Guru::create([
            'user_id'        => $this->guruPiketUser2->id,
            'nip'            => '19820202',
            'nama_lengkap'   => 'Guru Sesi Dua, M.Pd.',
            'mata_pelajaran' => 'Fisika',
            'status_aktif'   => true,
        ]);

        // Guru Biasa (Bukan guru piket hari ini)
        $this->guruBiasaUser = User::create([
            'name'     => 'Guru Biasa Non-Piket',
            'email'    => 'nonpiket@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '19830303',
            'password' => Hash::make('password'),
        ]);
        $this->guruBiasa = Guru::create([
            'user_id'        => $this->guruBiasaUser->id,
            'nip'            => '19830303',
            'nama_lengkap'   => 'Guru Biasa, S.Kom.',
            'mata_pelajaran' => 'Informatika',
            'status_aktif'   => true,
        ]);

        // Buat Jadwal Piket Jumat Sesi 1 & Sesi 2
        $this->jadwalSesi1 = JadwalPiket::create([
            'hari' => 5, // Jumat
            'nama_sesi' => 'Sesi 1',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:30:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'is_active' => true,
        ]);
        JadwalPiketGuru::create([
            'jadwal_piket_id' => $this->jadwalSesi1->id,
            'guru_id' => $this->guruPiket1->id,
        ]);

        $this->jadwalSesi2 = JadwalPiket::create([
            'hari' => 5, // Jumat
            'nama_sesi' => 'Sesi 2',
            'jam_mulai' => '09:30:00',
            'jam_selesai' => '14:00:00',
            'tanggal_mulai_berlaku' => '2026-01-01',
            'is_active' => true,
        ]);
        JadwalPiketGuru::create([
            'jadwal_piket_id' => $this->jadwalSesi2->id,
            'guru_id' => $this->guruPiket2->id,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Test 1: Guru Non-Piket masuk dalam Mode Pemantau di Dashboard & Pengajuan Index
     */
    public function test_1_guru_non_piket_masuk_mode_pemantau(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/PENDING/001',
            'kategori'    => 'izin',
            'alasan'      => 'Keperluan keluarga',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruBiasaUser)->get(route('guru.dashboard'));

        $response->assertOk();
        $response->assertSee('Mode Pemantau (Read-Only)');
        $response->assertSee('Khusus Guru Piket', false);

        // Di pengajuan index, tombol buat baru tidak muncul
        $responseIndex = $this->actingAs($this->guruBiasaUser)->get(route('guru.pengajuan.index'));
        $responseIndex->assertOk();
        $responseIndex->assertDontSee('Buat Pengajuan Baru');
    }

    /**
     * Test 2: Guru Non-Piket ditolak saat membuka form atau store dispensasi manual
     */
    public function test_2_guru_non_piket_ditolak_akses_buat_manual(): void
    {
        // Akses GET form create: dapat melihat halaman dalam mode terkunci (Mode Pemantau)
        $responseCreate = $this->actingAs($this->guruBiasaUser)->get(route('guru.pengajuan.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Formulir Terkunci');
        $responseCreate->assertSee('Mode Pemantau');
        $responseCreate->assertSee('Formulir Terkunci (Khusus Guru Piket Aktif)');

        // Akses POST store
        $payload = [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Izin urusan keluarga mendesak',
            'tujuan'          => 'Rumah',
            'jam_keluar'      => '08:00',
            'jam_kembali'     => '10:00',
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ];

        $responseStore = $this->actingAs($this->guruBiasaUser)->post(route('guru.pengajuan.store'), $payload);
        $responseStore->assertRedirect(route('guru.dashboard'));
        $responseStore->assertSessionHas('error');
    }

    /**
     * Test 3: Guru Non-Piket ditolak saat menyetujui atau menolak dispensasi siswa
     */
    public function test_3_guru_non_piket_ditolak_saat_approve_atau_reject(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/TEST/001',
            'kategori'    => 'izin',
            'alasan'      => 'Keperluan keluarga',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        // Coba approve oleh guru non-piket
        $responseApprove = $this->actingAs($this->guruBiasaUser)
            ->post(route('guru.pengajuan.approve', $dispensasi));
        $responseApprove->assertSessionHas('error');

        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);

        // Coba reject oleh guru non-piket
        $responseReject = $this->actingAs($this->guruBiasaUser)
            ->post(route('guru.pengajuan.reject', $dispensasi), [
                'catatan_admin' => 'Alasan penolakan pengujian',
            ]);
        $responseReject->assertSessionHas('error');

        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);
    }

    /**
     * Test 4: Guru Piket Sesi 1 aktif saat jam 08:00 WIB (Sesi 1 Berlangsung)
     */
    public function test_4_guru_piket_sesi_1_dapat_melihat_banner_aktif_dan_approve(): void
    {
        // Pukul 08:00 WIB = Sesi 1 Berlangsung
        $response = $this->actingAs($this->guruPiketUser1)->get(route('guru.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Bertugas Aktif');
        $response->assertSee('Sesi 1');

        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/SESI1/001',
            'kategori'    => 'izin',
            'alasan'      => 'Urusan sekolah di Sesi 1',
            'tujuan'      => 'Puskesmas',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $responseApprove = $this->actingAs($this->guruPiketUser1)
            ->post(route('guru.pengajuan.approve', $dispensasi));

        $responseApprove->assertRedirect(route('guru.pengajuan.index'));
        $responseApprove->assertSessionHas('success');

        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);
        $this->assertEquals($this->guruPiket1->id, $dispensasi->guru_id);
    }

    /**
     * Test 5: Guru Sesi 2 tidak bisa approve saat waktu masih Sesi 1 (08:00 WIB)
     */
    public function test_5_guru_sesi_2_tidak_bisa_approve_saat_sesi_1_berlangsung(): void
    {
        // Jam 08:00 WIB = Sesi 1, belum jam Sesi 2
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/SESI1/002',
            'kategori'    => 'izin',
            'alasan'      => 'Urusan pagi hari',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruPiketUser2)
            ->post(route('guru.pengajuan.approve', $dispensasi));

        $response->assertSessionHas('error');
        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);
    }

    /**
     * Test 6: Rollover - Saat waktu berganti ke Sesi 2 (10:00 WIB), Guru Sesi 2 dapat menyetujui pengajuan tertunda dari Sesi 1
     */
    public function test_6_guru_sesi_2_dapat_approve_pengajuan_tertinggal_dari_sesi_1(): void
    {
        // Siswa mengajukan di Sesi 1 (jam 08:00)
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/ROLLOVER/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan pagi belum sempat di-approve guru sesi 1',
            'tujuan'      => 'Perpustakaan Daerah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-3',
            'status'      => 'menunggu',
        ]);

        // Waktu berganti ke jam 10:00 WIB (Sesi 2 Berlangsung)
        Carbon::setTestNow(Carbon::parse('2026-10-16 10:00:00', 'Asia/Jakarta'));

        // Guru Sesi 1 sekarang sudah selesai jam piketnya, coba approve -> DITOLAK
        $responseSesi1 = $this->actingAs($this->guruPiketUser1)
            ->post(route('guru.pengajuan.approve', $dispensasi));
        $responseSesi1->assertSessionHas('error');

        $this->flushSession();

        // Guru Sesi 2 sekarang bertugas aktif -> DAPAT MENYETUJUI (Pelimpahan)
        $responseSesi2 = $this->actingAs($this->guruPiketUser2)
            ->post(route('guru.pengajuan.approve', $dispensasi));
        $responseSesi2->assertRedirect(route('guru.pengajuan.index'));
        $responseSesi2->assertSessionHas('success');

        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);
        $this->assertEquals($this->guruPiket2->id, $dispensasi->guru_id);
    }

    /**
     * Test 7: Pertukaran Piket (Swap) memindahkan hak approval ke Guru Pengganti
     */
    public function test_7_pertukaran_piket_memindahkan_hak_approval_ke_guru_pengganti(): void
    {
        // Jam 08:00 (Sesi 1)
        Carbon::setTestNow(Carbon::parse('2026-10-16 08:00:00', 'Asia/Jakarta'));

        // Guru Sesi 1 menukar jadwalnya ke Guru Biasa untuk hari ini
        PertukaranJadwalPiket::create([
            'jadwal_piket_id'    => $this->jadwalSesi1->id,
            'guru_asal_id'       => $this->guruPiket1->id,
            'guru_pengganti_id'  => $this->guruBiasa->id,
            'tanggal'            => '2026-10-16',
            'alasan'             => 'Ada tugas luar dinas',
            'status'             => 'disetujui',
            'is_active'          => true,
            'diproses_at'        => now(),
        ]);

        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/SWAP/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan saat shift digantikan',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        // Guru Asal (Guru Sesi 1) sekarang tidak bertugas -> DITOLAK
        $responseAsal = $this->actingAs($this->guruPiketUser1)
            ->post(route('guru.pengajuan.approve', $dispensasi));
        $responseAsal->assertSessionHas('error');

        $this->flushSession();

        // Guru Pengganti (Guru Biasa) sekarang sah memegang Sesi 1 -> BERHASIL
        $responsePengganti = $this->actingAs($this->guruBiasaUser)
            ->post(route('guru.pengajuan.approve', $dispensasi));
        $responsePengganti->assertRedirect(route('guru.pengajuan.index'));
        $responsePengganti->assertSessionHas('success');

        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);
        $this->assertEquals($this->guruBiasa->id, $dispensasi->guru_id);
    }
}
