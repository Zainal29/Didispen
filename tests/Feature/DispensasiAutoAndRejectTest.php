<?php

namespace Tests\Feature;

use App\Helpers\TimeHelper;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DispensasiAutoAndRejectTest extends TestCase
{
    use RefreshDatabase;

    private User $guruUser;
    private Guru $guru;
    private User $siswaUser;
    private Siswa $siswa;
    private Kelas $kelas;
    private Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Pastikan setting jam pelajaran default tersedia di DB
        Setting::set('jam_pelajaran', json_encode(TimeHelper::getDefaultJadwal()));

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII PPLG 1',
            'tingkat' => 'XII',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $this->guruUser = User::create([
            'name'     => 'Guru Piket Testing',
            'email'    => 'gurupiket@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198501012010011001',
            'password' => Hash::make('password'),
        ]);

        $this->guru = Guru::create([
            'user_id'        => $this->guruUser->id,
            'nip'            => '198501012010011001',
            'nama_lengkap'   => 'Guru Piket Testing, S.Kom.',
            'mata_pelajaran' => 'Pemrograman Web',
            'status_aktif'   => true,
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Siswa Testing',
            'email'    => 'siswa@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '555001',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->siswaUser->id,
            'kelas_id'     => $this->kelas->id,
            'jurusan_id'   => $this->jurusan->id,
            'nama_lengkap' => 'Siswa Testing',
            'nis_nip'      => '555001',
            'status_aktif' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * TEST A — Reject menghapus foto fisik dan mengosongkan kolom foto_verifikasi
     */
    public function test_a_reject_menghapus_foto_fisik_dan_mengosongkan_field(): void
    {
        $fotoPath = 'foto_verifikasi/test_selfie.jpg';
        Storage::disk('public')->put($fotoPath, 'fake image binary content');
        $this->assertTrue(Storage::disk('public')->exists($fotoPath));

        $dispensasi = Dispensasi::create([
            'siswa_id'        => $this->siswa->id,
            'nomor_surat'     => 'DISP/TEST/001',
            'kategori'        => 'izin',
            'alasan'          => 'Keperluan keluarga mendadak',
            'tujuan'          => 'Rumah',
            'jam_keluar'      => 'Jam Pelajaran ke-1',
            'jam_kembali'     => 'Jam Pelajaran ke-2',
            'foto_verifikasi' => $fotoPath,
            'status'          => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.reject', $dispensasi), [
                'catatan_admin' => 'Alasan tidak relevan dengan jam sekolah',
            ]);

        $response->assertRedirect(route('guru.pengajuan.index'));

        $dispensasi->refresh();
        $this->assertEquals('ditolak', $dispensasi->status);
        $this->assertNull($dispensasi->foto_verifikasi);
        $this->assertNotNull($dispensasi->rejected_at);
        $this->assertNull($dispensasi->approved_at);
        $this->assertEquals($this->guru->id, $dispensasi->guru_id);

        // Buktikan file fisik terhapus dari disk public
        $this->assertFalse(Storage::disk('public')->exists($fotoPath));

        // Buktikan record dispensasi, siswa, dan guru masih ada
        $this->assertDatabaseHas('dispensasi', ['id' => $dispensasi->id]);
        $this->assertDatabaseHas('siswa', ['id' => $this->siswa->id]);
        $this->assertDatabaseHas('guru', ['id' => $this->guru->id]);
    }

    /**
     * TEST B — Reject ketika file sudah tidak ada di storage tetap berhasil
     */
    public function test_b_reject_ketika_file_fisik_sudah_tidak_ada_tetap_berhasil(): void
    {
        $fotoPath = 'foto_verifikasi/missing_file.jpg';
        // File sengaja TIDAK dibuat di storage
        $this->assertFalse(Storage::disk('public')->exists($fotoPath));

        $dispensasi = Dispensasi::create([
            'siswa_id'        => $this->siswa->id,
            'nomor_surat'     => 'DISP/TEST/002',
            'kategori'        => 'izin',
            'alasan'          => 'Izin keperluan keluarga',
            'tujuan'          => 'Rumah',
            'jam_keluar'      => 'Jam Pelajaran ke-1',
            'jam_kembali'     => 'Jam Pelajaran ke-2',
            'foto_verifikasi' => $fotoPath,
            'status'          => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.reject', $dispensasi), [
                'catatan_admin' => 'Ditolak meskipun file foto fisik hilang',
            ]);

        $response->assertRedirect(route('guru.pengajuan.index'));

        $dispensasi->refresh();
        $this->assertEquals('ditolak', $dispensasi->status);
        $this->assertNull($dispensasi->foto_verifikasi);
        $this->assertNotNull($dispensasi->rejected_at);
        $this->assertDatabaseHas('dispensasi', ['id' => $dispensasi->id]);
    }

    /**
     * TEST C — Menunggu auto-complete setelah akhir KBM
     */
    public function test_c_menunggu_auto_complete_setelah_akhir_kbm(): void
    {
        // Senin, 15:30 WIB (KBM Senin selesai 15:15)
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:30:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'nomor_surat'          => 'DISP/TEST/003',
            'kategori'             => 'izin',
            'alasan'               => 'Menunggu persetujuan hingga akhir hari',
            'tujuan'               => 'Rumah Sakit',
            'jam_keluar'           => 'Jam Pelajaran ke-1',
            'jam_kembali'          => 'Jam Pelajaran ke-4',
            'status'               => 'menunggu',
            'approved_at'          => null,
            'rejected_at'          => null,
            'waktu_keluar_aktual'  => null,
            'waktu_kembali_aktual' => null,
            'created_at'           => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertNull($dispensasi->approved_at);
        $this->assertNull($dispensasi->rejected_at);
        $this->assertNull($dispensasi->waktu_keluar_aktual);
        $this->assertNull($dispensasi->waktu_kembali_aktual);
    }

    /**
     * TEST D — Disetujui auto-complete setelah akhir KBM
     */
    public function test_d_disetujui_auto_complete_setelah_akhir_kbm(): void
    {
        // Senin, 15:30 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:30:00', 'Asia/Jakarta'));
        $approvedTime = Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'guru_id'              => $this->guru->id,
            'nomor_surat'          => 'DISP/TEST/004',
            'kategori'             => 'izin',
            'alasan'               => 'Disetujui tapi siswa tidak jadi keluar',
            'tujuan'               => 'Perpustakaan Daerah',
            'jam_keluar'           => 'Jam Pelajaran ke-3',
            'jam_kembali'          => 'Jam Pelajaran ke-5',
            'status'               => 'disetujui',
            'approved_at'          => $approvedTime,
            'rejected_at'          => null,
            'waktu_keluar_aktual'  => null,
            'waktu_kembali_aktual' => null,
            'created_at'           => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertNotNull($dispensasi->approved_at);
        $this->assertEquals($approvedTime->format('Y-m-d H:i:s'), $dispensasi->approved_at->format('Y-m-d H:i:s'));
        $this->assertNull($dispensasi->waktu_keluar_aktual);
        $this->assertNull($dispensasi->waktu_kembali_aktual);
    }

    /**
     * TEST E — Keluar auto-complete setelah akhir KBM
     */
    public function test_e_keluar_auto_complete_setelah_akhir_kbm(): void
    {
        // Senin, 15:30 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:30:00', 'Asia/Jakarta'));
        $keluarTime = Carbon::parse('2026-09-28 09:30:00', 'Asia/Jakarta');
        $batasKembali = Carbon::parse('2026-09-28 11:45:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'guru_id'              => $this->guru->id,
            'nomor_surat'          => 'DISP/TEST/005',
            'kategori'             => 'izin',
            'alasan'               => 'Siswa keluar dan tidak kembali hingga sekolah bubar',
            'tujuan'               => 'Lomba',
            'jam_keluar'           => 'Jam Pelajaran ke-4',
            'jam_kembali'          => 'Jam Pelajaran ke-6',
            'status'               => 'keluar',
            'approved_at'          => $keluarTime->copy()->subHour(),
            'waktu_keluar_aktual'  => $keluarTime,
            'waktu_kembali_aktual' => null,
            'batas_waktu_kembali'  => $batasKembali,
            'created_at'           => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        // Timestamp keluar asli tetap utuh
        $this->assertNotNull($dispensasi->waktu_keluar_aktual);
        $this->assertStringContainsString('09:30', (string) $dispensasi->waktu_keluar_aktual);
        // Timestamp kembali TIDAK diisi palsu
        $this->assertNull($dispensasi->waktu_kembali_aktual);
        // Terlambat terdeteksi
        $this->assertTrue($dispensasi->is_warned);
    }

    /**
     * TEST F — Sebelum akhir KBM status tidak auto-complete
     */
    public function test_f_sebelum_akhir_kbm_tidak_auto_complete(): void
    {
        // Senin, 10:00 WIB (KBM Senin baru selesai 15:15)
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/TEST/006',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan pagi hari masih berjalan',
            'tujuan'      => 'Bank',
            'jam_keluar'  => 'Jam Pelajaran ke-3',
            'jam_kembali' => 'Jam Pelajaran ke-5',
            'status'      => 'menunggu',
            'created_at'  => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);
    }

    /**
     * TEST G — Ditolak tidak diubah oleh auto-complete
     */
    public function test_g_ditolak_tidak_diubah_oleh_auto_complete(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:30:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $this->guru->id,
            'nomor_surat' => 'DISP/TEST/007',
            'kategori'    => 'izin',
            'alasan'      => 'Ditolak sebelumnya',
            'tujuan'      => 'Keperluan Lain',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'ditolak',
            'rejected_at' => Carbon::now('Asia/Jakarta')->subHours(2),
            'created_at'  => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertEquals('ditolak', $dispensasi->status);
    }

    /**
     * TEST H — Selesai tidak diproses ulang (Idempotency)
     */
    public function test_h_selesai_tidak_diproses_ulang(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:30:00', 'Asia/Jakarta'));
        $originalKembali = Carbon::parse('2026-09-28 13:00:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'             => $this->siswa->id,
            'guru_id'              => $this->guru->id,
            'nomor_surat'          => 'DISP/TEST/008',
            'kategori'             => 'izin',
            'alasan'               => 'Sudah selesai siang hari',
            'tujuan'               => 'Puskesmas',
            'jam_keluar'           => 'Jam Pelajaran ke-2',
            'jam_kembali'          => 'Jam Pelajaran ke-4',
            'status'               => 'selesai',
            'waktu_kembali_aktual' => $originalKembali,
            'created_at'           => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertNotNull($dispensasi->waktu_kembali_aktual);
        $this->assertStringContainsString('13:00', (string) $dispensasi->waktu_kembali_aktual);
    }

    /**
     * TEST I — Batas kembali bukan threshold auto-complete
     */
    public function test_i_batas_kembali_bukan_threshold_auto_complete(): void
    {
        // Senin, jam 11:00 WIB (Batas kembali: 10:15 / Jam 4 selesai, tapi Akhir KBM Senin: 15:15)
        Carbon::setTestNow(Carbon::parse('2026-09-28 11:00:00', 'Asia/Jakarta'));
        $batasKembali = Carbon::parse('2026-09-28 10:15:00', 'Asia/Jakarta');

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => 'DISP/TEST/009',
            'kategori'            => 'izin',
            'alasan'              => 'Terlambat tapi KBM sekolah belum selesai',
            'tujuan'              => 'Kelurahan',
            'jam_keluar'          => 'Jam Pelajaran ke-2',
            'jam_kembali'         => 'Jam Pelajaran ke-4',
            'status'              => 'keluar',
            'waktu_keluar_aktual' => Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'),
            'batas_waktu_kembali' => $batasKembali,
            'created_at'          => Carbon::parse('2026-09-28 07:30:00', 'Asia/Jakarta'),
        ]);

        // Jalankan auto-complete pada 11:00 (batas kembali terlewati, tetapi akhir KBM belum)
        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        // Status harus TETAP 'keluar' (BELUM auto-complete)
        $this->assertEquals('keluar', $dispensasi->status);
        $this->assertTrue($dispensasi->isOverdue());

        // Sekarang maju ke pukul 15:16 WIB (melewati jam KBM terakhir 15:15)
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:16:00', 'Asia/Jakarta'));

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        // Sekarang status BERUBAH menjadi 'selesai'
        $this->assertEquals('selesai', $dispensasi->status);
        $this->assertNull($dispensasi->waktu_kembali_aktual);
        $this->assertTrue($dispensasi->is_warned);
    }

    /**
     * TEST J — Jadwal dinamis dari database mengubah threshold auto-complete
     */
    public function test_j_jadwal_dinamis_mengubah_threshold_auto_complete(): void
    {
        // Ubah jadwal Senin: Jam ke-10 selesai pukul 16:00 (bukan 15:15 default)
        $jadwal = TimeHelper::getDefaultJadwal();
        $jadwal['senin_selasa'][10]['end'] = '16:00';
        Setting::set('jam_pelajaran', json_encode($jadwal));

        // Senin, 15:30 WIB (Sudah lewat 15:15 default, tapi belum lewat 16:00 setting baru)
        Carbon::setTestNow(Carbon::parse('2026-09-28 15:30:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/TEST/010',
            'kategori'    => 'izin',
            'alasan'      => 'Uji threshold dinamis',
            'tujuan'      => 'Dinas',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-5',
            'status'      => 'menunggu',
            'created_at'  => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        // Harus TETAP menunggu karena KBM berakhir 16:00
        $this->assertEquals('menunggu', $dispensasi->status);

        // Maju ke 16:01 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-28 16:01:00', 'Asia/Jakarta'));

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();

        $dispensasi->refresh();
        // Sekarang harus SELESAI
        $this->assertEquals('selesai', $dispensasi->status);
    }

    /**
     * TEST K — Hari berbeda (Jumat, Sabtu, Minggu, Senin) menggunakan jam selesai KBM masing-masing
     */
    public function test_k_hari_berbeda_menggunakan_jam_selesai_kbm_masing_masing(): void
    {
        // 1. Jumat (2026-10-02): KBM selesai pukul 14:00 (Jam 8 selesai 14:00)
        Carbon::setTestNow(Carbon::parse('2026-10-02 13:50:00', 'Asia/Jakarta'));
        $dispJumat = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/JUMAT/001',
            'kategori'    => 'izin',
            'alasan'      => 'Uji hari Jumat',
            'tujuan'      => 'Masjid',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
            'created_at'  => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();
        $dispJumat->refresh();
        $this->assertEquals('menunggu', $dispJumat->status); // 13:50 < 14:00

        Carbon::setTestNow(Carbon::parse('2026-10-02 14:05:00', 'Asia/Jakarta'));
        $this->artisan('dispensasi:auto-complete')->assertSuccessful();
        $dispJumat->refresh();
        $this->assertEquals('selesai', $dispJumat->status); // 14:05 >= 14:00

        // 2. Sabtu (2026-10-03): KBM selesai pukul 15:15
        Carbon::setTestNow(Carbon::parse('2026-10-03 14:30:00', 'Asia/Jakarta'));
        $dispSabtu = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/SABTU/001',
            'kategori'    => 'izin',
            'alasan'      => 'Uji hari Sabtu',
            'tujuan'      => 'Eskul',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
            'created_at'  => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();
        $dispSabtu->refresh();
        $this->assertEquals('menunggu', $dispSabtu->status); // 14:30 < 15:15

        Carbon::setTestNow(Carbon::parse('2026-10-03 15:20:00', 'Asia/Jakarta'));
        $this->artisan('dispensasi:auto-complete')->assertSuccessful();
        $dispSabtu->refresh();
        $this->assertEquals('selesai', $dispSabtu->status); // 15:20 >= 15:15

        // 3. Minggu (2026-10-04): KBM selesai pukul 15:15
        Carbon::setTestNow(Carbon::parse('2026-10-04 14:30:00', 'Asia/Jakarta'));
        $dispMinggu = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DISP/MINGGU/001',
            'kategori'    => 'izin',
            'alasan'      => 'Uji hari Minggu',
            'tujuan'      => 'Latihan',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
            'created_at'  => Carbon::now('Asia/Jakarta'),
        ]);

        $this->artisan('dispensasi:auto-complete')->assertSuccessful();
        $dispMinggu->refresh();
        $this->assertEquals('menunggu', $dispMinggu->status); // 14:30 < 15:15

        Carbon::setTestNow(Carbon::parse('2026-10-04 15:20:00', 'Asia/Jakarta'));
        $this->artisan('dispensasi:auto-complete')->assertSuccessful();
        $dispMinggu->refresh();
        $this->assertEquals('selesai', $dispMinggu->status); // 15:20 >= 15:15
    }
}
