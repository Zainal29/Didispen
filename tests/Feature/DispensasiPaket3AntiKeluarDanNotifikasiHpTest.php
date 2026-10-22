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

class DispensasiPaket3AntiKeluarDanNotifikasiHpTest extends TestCase
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
            'name'     => 'Bayu Pratama',
            'email'    => 'bayu@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->userSiswa->id,
            'nama_lengkap' => 'Bayu Pratama',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
        ]);
    }

    /**
     * 1. Status 'menunggu': Komponen merender peringatan Guru Piket & Pengingat WhatsApp
     */
    public function test_komponen_anti_keluar_merender_peringatan_menunggu_guru_piket(): void
    {
        Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/WAIT01',
            'kategori'             => 'izin',
            'alasan'               => 'Urusan penting',
            'tujuan'               => 'Rumah',
            'tipe_pengajuan'       => 'individu',
            'tanggal_pengajuan'    => Carbon::today(),
            'jam_keluar'           => 1,
            'jam_kembali'          => 3,
            'status'               => 'menunggu',
            'status_persetujuan'   => 'menunggu',
        ]);

        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertSee('didispenAntiKeluarContainer');
        $response->assertSee('Jangan tutup web! Segera temui Guru Piket (Sisa 20 Menit)');
        $response->assertSee('Pesan resmi dikirim via WhatsApp DIDISPEN');
        $response->assertSee('beforeunload');
        $response->assertSee('visibilitychange');
    }

    /**
     * 2. Status 'disetujui': Komponen merender peringatan Segera ke Pos Satpam 20 menit & Wajib Helm
     */
    public function test_komponen_anti_keluar_merender_peringatan_disetujui_pos_satpam_dan_helm(): void
    {
        Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/ACC01',
            'kategori'             => 'izin',
            'alasan'               => 'Keperluan lomba',
            'tujuan'               => 'Dinas Pendidikan',
            'tipe_pengajuan'       => 'individu',
            'tanggal_pengajuan'    => Carbon::today(),
            'jam_keluar'           => 1,
            'jam_kembali'          => 4,
            'status'               => 'disetujui',
            'status_persetujuan'   => 'disetujui',
            'approved_at'          => Carbon::now(),
        ]);

        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertSee('didispenAntiKeluarContainer');
        $response->assertSee('Segera ke Pos Satpam (Sisa 20 Menit). Wajib pakai helm!');
        $response->assertSee('Wajib memakai helm SNI dan tertib berlalu lintas!');
        $response->assertSee('Pesan resmi dikirim via WhatsApp DIDISPEN');
    }

    /**
     * 3. Status 'keluar': Komponen merender peringatan Hati-hati di jalan, wajib helm & Batas Kembali
     */
    public function test_komponen_anti_keluar_merender_peringatan_keluar_dan_helm(): void
    {
        Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/OUT01',
            'kategori'             => 'izin',
            'alasan'               => 'Mewakili sekolah',
            'tujuan'               => 'Stadion Gelora',
            'tipe_pengajuan'       => 'individu',
            'tanggal_pengajuan'    => Carbon::today(),
            'jam_keluar'           => 1,
            'jam_kembali'          => 4,
            'status'               => 'keluar',
            'status_persetujuan'   => 'disetujui',
            'scan_keluar_at'       => Carbon::now(),
        ]);

        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertSee('didispenAntiKeluarContainer');
        $response->assertSee('Hati-hati di jalan, wajib helm. Batas kembali pukul');
        $response->assertSee('Pesan resmi dikirim via WhatsApp DIDISPEN');
    }

    /**
     * 4. Tanpa dispensasi aktif: Komponen anti-keluar TIDAK dirender
     */
    public function test_komponen_anti_keluar_tidak_dirender_jika_tidak_ada_dispensasi_aktif(): void
    {
        // Hanya ada dispensasi yang sudah 'selesai'
        Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/DONE01',
            'kategori'             => 'izin',
            'alasan'               => 'Sudah selesai kemarin',
            'tujuan'               => 'Rumah',
            'tipe_pengajuan'       => 'individu',
            'tanggal_pengajuan'    => Carbon::yesterday(),
            'jam_keluar'           => 1,
            'jam_kembali'          => 3,
            'status'               => 'selesai',
            'status_persetujuan'   => 'disetujui',
        ]);

        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertDontSee('didispenAntiKeluarContainer');
    }

    /**
     * 5. Anggota rombongan: Komponen anti-keluar juga aktif jika siswa terdaftar sebagai anggota rombongan
     */
    public function test_komponen_anti_keluar_aktif_untuk_anggota_rombongan(): void
    {
        $userKetua = User::create([
            'name'     => 'Ketua Rombongan',
            'email'    => 'ketua@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);

        $pemohon = Siswa::create([
            'user_id'      => $userKetua->id,
            'nama_lengkap' => 'Ketua Rombongan',
            'kelas_id'     => $this->siswa->kelas_id,
            'jurusan_id'   => $this->siswa->jurusan_id,
        ]);

        $disp = Dispensasi::create([
            'siswa_id'             => $pemohon->id,
            'nomor_surat'          => 'DISP/TEST/GRP01',
            'kategori'             => 'tugas',
            'alasan'               => 'Lomba LKS',
            'tujuan'               => 'Semarang',
            'tipe_pengajuan'       => 'rombongan',
            'tanggal_pengajuan'    => Carbon::today(),
            'jam_keluar'           => 1,
            'jam_kembali'          => 5,
            'status'               => 'disetujui',
            'status_persetujuan'   => 'disetujui',
            'approved_at'          => Carbon::now(),
        ]);

        // Hubungkan siswa ke rombongan
        $disp->anggotaSiswa()->attach($this->siswa->id);

        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertSee('didispenAntiKeluarContainer');
        $response->assertSee('Segera ke Pos Satpam (Sisa 20 Menit). Wajib pakai helm!');
    }
}
