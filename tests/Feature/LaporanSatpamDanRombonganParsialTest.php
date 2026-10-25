<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\LaporanSatpam;
use App\Models\Siswa;
use App\Models\User;
use App\Services\QRScanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaporanSatpamDanRombonganParsialTest extends TestCase
{
    use RefreshDatabase;

    private User $satpam;
    private User $admin;
    private User $guruUser;
    private Guru $guru;
    private Siswa $siswaKetua;
    private Siswa $siswaAnggota1;
    private Siswa $siswaAnggota2;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::today('Asia/Jakarta')->setTime(9, 0, 0));

        $this->satpam = User::create([
            'name'     => 'Pak Satpam',
            'email'    => 'satpam@didispen.test',
            'role'     => 'satpam',
            'password' => Hash::make('password'),
        ]);

        $this->admin = User::create([
            'name'     => 'Admin Sekolah',
            'email'    => 'admin@didispen.test',
            'role'     => 'admin',
            'password' => Hash::make('password'),
        ]);

        $this->guruUser = User::create([
            'name'                => 'Guru Piket',
            'email'               => 'guru@didispen.test',
            'role'                => 'guru',
            'password'            => Hash::make('secret123456'),
            'password_changed_at' => now(),
            'no_wa'               => '08123456789',
        ]);

        $this->guru = Guru::create([
            'user_id'      => $this->guruUser->id,
            'nama_lengkap' => 'Guru Piket S.Pd',
            'nip'          => '198501012010011001',
            'no_telepon'   => '08123456789',
        ]);

        $jurusan = Jurusan::create(['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'kode_jurusan' => 'RPL']);
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        $u1 = User::create(['name' => 'Ahmad Ketua', 'email' => 'ahmad@test.id', 'role' => 'siswa', 'password' => Hash::make('pass'), 'nis_nip' => '1001']);
        $this->siswaKetua = Siswa::create(['user_id' => $u1->id, 'kelas_id' => $kelas->id, 'jurusan_id' => $jurusan->id, 'nama_lengkap' => 'Ahmad Ketua']);

        $u2 = User::create(['name' => 'Budi Anggota', 'email' => 'budi@test.id', 'role' => 'siswa', 'password' => Hash::make('pass'), 'nis_nip' => '1002']);
        $this->siswaAnggota1 = Siswa::create(['user_id' => $u2->id, 'kelas_id' => $kelas->id, 'jurusan_id' => $jurusan->id, 'nama_lengkap' => 'Budi Anggota']);

        $u3 = User::create(['name' => 'Citra Anggota', 'email' => 'citra@test.id', 'role' => 'siswa', 'password' => Hash::make('pass'), 'nis_nip' => '1003']);
        $this->siswaAnggota2 = Siswa::create(['user_id' => $u3->id, 'kelas_id' => $kelas->id, 'jurusan_id' => $jurusan->id, 'nama_lengkap' => 'Citra Anggota']);
    }

    public function test_satpam_bisa_membuat_laporan_temuan_gerbang_dan_terhubung_ke_admin(): void
    {
        $response = $this->actingAs($this->satpam)->post(route('satpam.laporan.store'), [
            'siswa_id'      => $this->siswaAnggota1->id,
            'kategori'      => 'terobos_gerbang',
            'keterangan'    => 'Siswa mencoba keluar tanpa membawa surat dispensasi yang sah.',
        ]);

        $response->assertRedirect(route('satpam.laporan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('laporan_satpam', [
            'satpam_id'  => $this->satpam->id,
            'siswa_id'   => $this->siswaAnggota1->id,
            'kategori'   => 'terobos_gerbang',
            'status'     => 'menunggu',
        ]);

        // Cek bahwa admin bisa melihat laporan tersebut
        $this->flushSession();
        $adminResp = $this->actingAs($this->admin)->get(route('admin.laporan-satpam.index'));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('BUDI ANGGOTA');
        $adminResp->assertSee('Keluar Tanpa Izin / Terobos Gerbang');

        // Admin menindaklanjuti laporan
        $laporan = LaporanSatpam::first();
        $tindakResp = $this->actingAs($this->admin)->post(route('admin.laporan-satpam.tindak-lanjuti', $laporan->id), [
            'status'                => 'selesai',
            'catatan_tindak_lanjut' => 'Siswa telah dipanggil ke BK dan diberikan pembinaan.',
        ]);

        $tindakResp->assertRedirect();
        $this->assertDatabaseHas('laporan_satpam', [
            'id'                    => $laporan->id,
            'status'                => 'selesai',
            'tindak_lanjut_oleh'    => $this->admin->id,
            'catatan_tindak_lanjut' => 'Siswa telah dipanggil ke BK dan diberikan pembinaan.',
        ]);
    }

    public function test_satpam_bisa_search_siswa_via_ajax(): void
    {
        $response = $this->actingAs($this->satpam)->get(route('satpam.laporan.search-siswa', ['q' => 'Ahmad']));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'nama_lengkap' => 'AHMAD KETUA',
        ]);
    }

    public function test_scan_kembali_rombongan_parsial_otomatis_mencatat_laporan_temuan_untuk_siswa_yang_tidak_kembali(): void
    {
        // 1. Buat dispensasi rombongan (1 ketua + 2 anggota)
        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswaKetua->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => 'DSP/ROMBONGAN/2026/001',
            'tipe_pengajuan'      => 'rombongan',
            'kategori'            => 'kegiatan_sekolah',
            'alasan'              => 'Lomba Olimpiade Sains',
            'tujuan'              => 'Gedung Olahraga',
            'tanggal_izin'        => today('Asia/Jakarta'),
            'jam_keluar'          => '09:00',
            'jam_kembali'         => '12:00',
            'batas_waktu_kembali' => today('Asia/Jakarta')->setTime(12, 0, 0),
            'status'              => 'keluar',
            'is_warned'           => false,
            'waktu_keluar_aktual' => now('Asia/Jakarta')->subHours(2),
            'satpam_keluar_id'    => $this->satpam->id,
            'qr_token'            => 'token-rombongan-12345',
        ]);

        $dispensasi->anggotaSiswa()->attach([$this->siswaAnggota1->id, $this->siswaAnggota2->id]);

        // 2. Scan kembali: Hanya Ahmad (Ketua) dan Budi (Anggota 1) yang kembali. Citra (Anggota 2) tidak ikut kembali!
        $scanService = app(QRScanService::class);
        $result = $scanService->processKembali($dispensasi, $this->satpam->id, [
            'siswa_kembali_ids' => [$this->siswaKetua->id, $this->siswaAnggota1->id],
            'siswa_tidak_kembali' => [
                [
                    'siswa_id' => $this->siswaAnggota2->id,
                    'nama'     => 'Citra Anggota',
                    'alasan'   => 'Langsung Pulang ke Rumah',
                    'catatan'  => 'Ditanya satpam, ketua menyatakan Citra langsung pulang ke rumah naik ojol.',
                ],
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('kembali', $result['action']);
        $this->assertEquals(1, $result['laporan_temuan_count']);

        // 3. Pastikan status dispensasi utama selesai
        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);

        // 4. Pastikan status_kembali di pivot terisi dengan tepat
        $this->assertDatabaseHas('dispensasi_anggota', [
            'dispensasi_id'  => $dispensasi->id,
            'siswa_id'       => $this->siswaAnggota1->id,
            'status_kembali' => 'kembali',
        ]);

        $this->assertDatabaseHas('dispensasi_anggota', [
            'dispensasi_id'  => $dispensasi->id,
            'siswa_id'       => $this->siswaAnggota2->id,
            'status_kembali' => 'tidak_kembali',
            'catatan_kembali' => 'Ditanya satpam, ketua menyatakan Citra langsung pulang ke rumah naik ojol.',
        ]);

        // 5. Pastikan LaporanSatpam otomatis terbentuk untuk Citra
        $this->assertDatabaseHas('laporan_satpam', [
            'satpam_id'     => $this->satpam->id,
            'siswa_id'      => $this->siswaAnggota2->id,
            'dispensasi_id' => $dispensasi->id,
            'kategori'      => 'rombongan_tidak_kembali',
            'status'        => 'menunggu',
        ]);

        // 6. Guru Piket bisa melihat temuan ini di panel guru
        $this->flushSession();
        $guruResp = $this->actingAs($this->guruUser)->get(route('guru.laporan-satpam.index'));
        $guruResp->assertStatus(200);
        $guruResp->assertSee('CITRA ANGGOTA');
        $guruResp->assertSee('Dispensasi Rombongan: Siswa Tidak Kembali (Lapor Wali Kelas)');
        $guruResp->assertSee('Tindakan / Respon Wali Kelas');
    }

    public function test_satpam_dapat_menghapus_laporan_temuan(): void
    {
        $lap = LaporanSatpam::create([
            'satpam_id' => $this->satpam->id,
            'siswa_id'  => $this->siswaKetua->id,
            'kategori'  => 'terobos_gerbang',
            'keterangan'=> 'Siswa melompat pagar samping',
            'status'    => 'menunggu',
        ]);

        $this->actingAs($this->satpam)
            ->delete(route('satpam.laporan.destroy', $lap))
            ->assertRedirect(route('satpam.laporan.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('laporan_satpam', ['id' => $lap->id]);
    }

    public function test_admin_dapat_menghapus_laporan_temuan(): void
    {
        $lap = LaporanSatpam::create([
            'satpam_id' => $this->satpam->id,
            'siswa_id'  => $this->siswaKetua->id,
            'kategori'  => 'pelanggaran_lain',
            'keterangan'=> 'Siswa tidak memakai atribut lengkap',
            'status'    => 'menunggu',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.laporan-satpam.destroy', $lap))
            ->assertRedirect(route('admin.laporan-satpam.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('laporan_satpam', ['id' => $lap->id]);
    }

    public function test_admin_dan_guru_dapat_memberikan_tindakan_respon_wali_kelas(): void
    {
        $lap = LaporanSatpam::create([
            'satpam_id' => $this->satpam->id,
            'siswa_id'  => $this->siswaKetua->id,
            'kategori'  => 'rombongan_tidak_kembali',
            'keterangan'=> 'Siswa tidak ikut rombongan kembali',
            'status'    => 'menunggu',
        ]);

        // Guru Piket tindak lanjuti atas nama Wali Kelas
        $this->actingAs($this->guruUser)
            ->post(route('guru.laporan-satpam.tindak-lanjuti', $lap), [
                'status'                 => 'ditindaklanjuti',
                'catatan_tindak_lanjut'  => 'Wali kelas sedang menghubungi orang tua siswa.',
            ])
            ->assertRedirect(route('guru.laporan-satpam.index'))
            ->assertSessionHas('success');

        $lap->refresh();
        $this->assertEquals('ditindaklanjuti', $lap->status);
        $this->assertEquals('Wali kelas sedang menghubungi orang tua siswa.', $lap->catatan_tindak_lanjut);
        $this->assertEquals($this->guruUser->id, $lap->tindak_lanjut_oleh);

        // Admin selesaikan
        $this->flushSession();
        $this->actingAs($this->admin)
            ->post(route('admin.laporan-satpam.tindak-lanjuti', $lap), [
                'status'                 => 'selesai',
                'catatan_tindak_lanjut'  => 'Sudah dikonfirmasi Wali Kelas: Orang tua mengonfirmasi anak sakit di rumah.',
            ])
            ->assertRedirect(route('admin.laporan-satpam.index'))
            ->assertSessionHas('success');

        $lap->refresh();
        $this->assertEquals('selesai', $lap->status);
        $this->assertEquals($this->admin->id, $lap->tindak_lanjut_oleh);
    }
}

