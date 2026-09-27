<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DispensasiContactSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $guruUser;
    private Guru $guru;
    private User $satpamUser;
    private Siswa $siswa;
    private Dispensasi $dispensasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guruUser = User::create([
            'name'     => 'Guru Penguji',
            'email'    => 'gurupenguji@sch.id',
            'role'     => 'guru',
            'password' => Hash::make('password'),
        ]);

        $this->guru = Guru::create([
            'user_id'      => $this->guruUser->id,
            'nama_lengkap' => 'Guru Penguji',
            'nip'          => '198501012010011002',
            'status_aktif' => true,
        ]);

        $this->satpamUser = User::create([
            'name'     => 'Satpam Penguji',
            'email'    => 'satpampenguji@sch.id',
            'role'     => 'satpam',
            'password' => Hash::make('password'),
        ]);

        $jurusan = Jurusan::create(['nama_jurusan' => 'RPL', 'kode_jurusan' => 'RPL']);
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);
        $siswaUser = User::create([
            'name'     => 'Siswa Penguji',
            'email'    => 'siswapenguji@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);
        $this->siswa = Siswa::create([
            'user_id'      => $siswaUser->id,
            'nama_lengkap' => 'Siswa Penguji',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
            'no_telepon'   => '081234567890',
            'status_aktif' => true,
        ]);

        $this->dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => 'TEST/SYNC/' . random_int(1000, 9999),
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian kontak sinkron',
            'tujuan'              => 'Keperluan keluarga',
            'jam_keluar'          => 'Jam Pelajaran ke-1',
            'jam_kembali'         => 'Jam Pelajaran ke-2',
            'batas_waktu_kembali' => now()->subMinutes(20),
            'status'              => 'keluar',
            'waktu_keluar_aktual' => now()->subHour(),
            'is_warned'           => false,
            'warned_at'           => null,
        ]);
    }

    /**
     * Test 1 — Guru menghubungi menandai is_warned = true dan warned_at !== null
     */
    public function test_guru_menghubungi_menandai_is_warned_dan_warned_at(): void
    {
        $response = $this->actingAs($this->guruUser)
            ->postJson("/guru/dispensasi/{$this->dispensasi->id}/wa-contacted");

        $response->assertOk()->assertJson(['success' => true]);

        $this->dispensasi->refresh();
        $this->assertTrue((bool) $this->dispensasi->is_warned);
        $this->assertNotNull($this->dispensasi->warned_at);
    }

    /**
     * Test 2 — Satpam menghubungi menandai is_warned = true dan warned_at !== null
     */
    public function test_satpam_menghubungi_menandai_is_warned_dan_warned_at(): void
    {
        $response = $this->actingAs($this->satpamUser)
            ->postJson("/satpam/dispensasi/{$this->dispensasi->id}/wa-contacted");

        $response->assertOk()->assertJson(['success' => true]);

        $this->dispensasi->refresh();
        $this->assertTrue((bool) $this->dispensasi->is_warned);
        $this->assertNotNull($this->dispensasi->warned_at);
    }

    /**
     * Test 3 — Guru -> Satpam sinkron
     * Guru menandai kontak, lalu Satpam mengakses dashboard & card, indikator kontak tampil.
     */
    public function test_guru_contact_synchronizes_to_satpam_views(): void
    {
        $this->actingAs($this->guruUser)
            ->postJson("/guru/dispensasi/{$this->dispensasi->id}/wa-contacted")
            ->assertOk();

        // Satpam Dashboard
        $this->flushSession();
        $responseSatpam = $this->actingAs($this->satpamUser)
            ->get(route('satpam.dashboard'));
        $responseSatpam->assertOk();
        $responseSatpam->assertSee('Sudah dihubungi via WhatsApp');
        $responseSatpam->assertSee('DIHUBUNGI');

        // Satpam Detail
        $responseDetail = $this->actingAs($this->satpamUser)
            ->get(route('satpam.dispensasi.detail', $this->dispensasi));

        $responseDetail->assertOk();
        $responseDetail->assertSee('Sudah Dihubungi');
    }

    /**
     * Test 4 — Satpam -> Guru sinkron
     * Satpam menandai kontak, lalu Guru mengakses dashboard & detail, indikator kontak tampil.
     */
    public function test_satpam_contact_synchronizes_to_guru_views(): void
    {
        $this->actingAs($this->satpamUser)
            ->postJson("/satpam/dispensasi/{$this->dispensasi->id}/wa-contacted")
            ->assertOk();

        // Guru Dashboard
        $this->flushSession();
        $responseGuru = $this->actingAs($this->guruUser)
            ->get(route('guru.dashboard'));

        $responseGuru->assertOk();
        $responseGuru->assertSee('Sudah Dihubungi');

        // Guru Detail
        $responseDetail = $this->actingAs($this->guruUser)
            ->get(route('guru.pengajuan.show', $this->dispensasi));

        $responseDetail->assertOk();
        $responseDetail->assertSee('Sudah Dihubungi');
    }

    /**
     * Test 5 — Belum dihubungi: jika is_warned = false, tidak menampilkan status sudah dihubungi
     */
    public function test_belum_dihubungi_tidak_menampilkan_status_sudah_dihubungi(): void
    {
        // Guru views
        $responseGuru = $this->actingAs($this->guruUser)
            ->get(route('guru.dashboard'));
        $responseGuru->assertOk();
        $responseGuru->assertDontSee('<i class="fas fa-phone-alt mr-1"></i>Sudah Dihubungi', false);
        $responseGuru->assertDontSee('<span class="wa-text">Sudah Dihubungi</span>', false);
        $responseGuru->assertSee('<span class="wa-text">Hubungi</span>', false);

        $responseGuruDetail = $this->actingAs($this->guruUser)
            ->get(route('guru.pengajuan.show', $this->dispensasi));
        $responseGuruDetail->assertOk();
        $responseGuruDetail->assertDontSee('Sudah Dihubungi');

        // Satpam views
        $this->flushSession();
        $responseSatpam = $this->actingAs($this->satpamUser)
            ->get(route('satpam.dashboard'));
        $responseSatpam->assertOk();
        $responseSatpam->assertDontSee('class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-purple-100 text-purple-700 uppercase"', false);
        $responseSatpam->assertDontSee('<p class="text-[10px] text-green-600 font-medium">', false);
        $responseSatpam->assertSee('handleWaContacted(' . $this->dispensasi->id, false);

        $responseSatpamDetail = $this->actingAs($this->satpamUser)
            ->get(route('satpam.dispensasi.detail', $this->dispensasi));
        $responseSatpamDetail->assertOk();
        $responseSatpamDetail->assertDontSee('Sudah Dihubungi');
    }

    /**
     * Test 6 — Tanpa nomor telepon: no_telepon = null & is_warned = true
     * Tetap menampilkan indikator Sudah Dihubungi pada Guru dashboard.
     */
    public function test_tanpa_nomor_telepon_tetap_menampilkan_sudah_dihubungi_di_guru(): void
    {
        $this->siswa->update(['no_telepon' => null]);
        $this->dispensasi->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);

        $responseGuru = $this->actingAs($this->guruUser)
            ->get(route('guru.dashboard'));

        $responseGuru->assertOk();
        $responseGuru->assertSee('Sudah Dihubungi');

        $responseDetail = $this->actingAs($this->guruUser)
            ->get(route('guru.pengajuan.show', $this->dispensasi));

        $responseDetail->assertOk();
        $responseDetail->assertSee('Sudah Dihubungi');
    }

    /**
     * Test 7 — Contact tidak mengubah status dispensasi (tetap keluar, tidak selesai)
     */
    public function test_contact_tidak_mengubah_status_dispensasi(): void
    {
        $this->assertEquals('keluar', $this->dispensasi->status);

        $this->actingAs($this->guruUser)
            ->postJson("/guru/dispensasi/{$this->dispensasi->id}/wa-contacted")
            ->assertOk();

        $this->dispensasi->refresh();
        $this->assertEquals('keluar', $this->dispensasi->status);
        $this->assertNotEquals('selesai', $this->dispensasi->status);
    }

    /**
     * Test 8 — Contact tidak mengubah timestamp waktu_keluar_aktual / waktu_kembali_aktual
     */
    public function test_contact_tidak_mengubah_timestamp(): void
    {
        $originalWaktuKeluar = $this->dispensasi->waktu_keluar_aktual;
        $originalWaktuKembali = $this->dispensasi->waktu_kembali_aktual;

        $this->actingAs($this->satpamUser)
            ->postJson("/satpam/dispensasi/{$this->dispensasi->id}/wa-contacted")
            ->assertOk();

        $this->dispensasi->refresh();
        $this->assertEquals($originalWaktuKeluar, $this->dispensasi->waktu_keluar_aktual);
        $this->assertEquals($originalWaktuKembali, $this->dispensasi->waktu_kembali_aktual);
    }

    /**
     * Test 9 — Contact tidak menghapus foto bukti atau foto verifikasi
     */
    public function test_contact_tidak_menghapus_foto(): void
    {
        Storage::fake('public');

        $fotoVerifPath = 'dispensasi/verifikasi/test_verif.jpg';
        $fotoBuktiPath = 'dispensasi/bukti/test_bukti.jpg';

        Storage::disk('public')->put($fotoVerifPath, 'dummy-verif-content');
        Storage::disk('public')->put($fotoBuktiPath, 'dummy-bukti-content');

        $this->dispensasi->update([
            'foto_verifikasi' => $fotoVerifPath,
            'foto_bukti'      => $fotoBuktiPath,
        ]);

        $this->actingAs($this->guruUser)
            ->postJson("/guru/dispensasi/{$this->dispensasi->id}/wa-contacted")
            ->assertOk();

        $this->dispensasi->refresh();
        $this->assertEquals($fotoVerifPath, $this->dispensasi->foto_verifikasi);
        $this->assertEquals($fotoBuktiPath, $this->dispensasi->foto_bukti);

        Storage::disk('public')->assertExists($fotoVerifPath);
        Storage::disk('public')->assertExists($fotoBuktiPath);
    }

    /**
     * Test 10 — Filter dihubungi pada Guru Dashboard
     */
    public function test_guru_filter_dihubungi_menampilkan_hanya_dispensasi_yang_sudah_dihubungi(): void
    {
        $dispensasiBelum = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => 'TEST/UNWARNED/' . random_int(1000, 9999),
            'kategori'            => 'izin',
            'alasan'              => 'Belum dihubungi',
            'tujuan'              => 'Toko',
            'jam_keluar'          => 'Jam Pelajaran ke-1',
            'jam_kembali'         => 'Jam Pelajaran ke-2',
            'batas_waktu_kembali' => now()->subMinutes(10),
            'status'              => 'keluar',
            'is_warned'           => false,
        ]);

        $this->dispensasi->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);

        $response = $this->actingAs($this->guruUser)
            ->get(route('guru.dashboard', ['filter' => 'dihubungi']));

        $response->assertOk();
        $response->assertSee($this->dispensasi->nomor_surat);
        $response->assertDontSee($dispensasiBelum->nomor_surat);
    }
}
