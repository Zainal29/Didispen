<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiswaDispensasiLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $siswaUser;
    private Siswa $siswa;
    private User $satpamUser;
    private Kelas $kelas;
    private Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        // Bekukan waktu pada hari Rabu jam 09:00 WIB agar validasi jam operasional dispensasi selalu lolos
        Carbon::setTestNow(Carbon::parse('2026-09-23 09:00:00', 'Asia/Jakarta'));

        Storage::fake('public');

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kode_jurusan' => 'RPL',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Siswa Lifecycle Test',
            'email'    => 'siswa_lifecycle@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '12345678',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->siswaUser->id,
            'nama_lengkap' => 'Siswa Lifecycle Test',
            'nis_nip'      => '12345678',
            'kelas_id'     => $this->kelas->id,
            'jurusan_id'   => $this->jurusan->id,
            'no_telepon'   => '081234567890',
            'status_aktif' => true,
        ]);

        $this->satpamUser = User::create([
            'name'     => 'Satpam Lifecycle Test',
            'email'    => 'satpam_lifecycle@sch.id',
            'role'     => 'satpam',
            'password' => Hash::make('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'kategori'        => 'izin',
            'alasan'          => 'Keperluan keluarga mendesak dan penting sekali',
            'tujuan'          => 'Kantor Kelurahan',
            'lokasi'          => 'Jl. Merdeka No. 12',
            'no_telepon'      => '081234567890',
            'jam_keluar'      => 1,
            'jam_kembali'     => 2,
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg', 600, 600),
        ], $overrides);
    }

    public function test_siswa_tidak_bisa_buat_pengajuan_saat_punya_pengajuan_menunggu(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'TEST/WAIT/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan pertama sedang menunggu',
            'tujuan'      => 'Klinik',
            'jam_keluar'  => 'Jam Ke-1',
            'jam_kembali' => 'Jam Ke-2',
            'status'      => 'menunggu',
        ]);

        $response = $this->actingAs($this->siswaUser)
            ->post('/siswa/pengajuan', $this->validStorePayload());

        $response->assertSessionHasErrors('kategori');
        $this->assertCount(1, Dispensasi::where('siswa_id', $this->siswa->id)->get());
    }

    public function test_siswa_tidak_bisa_buat_pengajuan_saat_punya_pengajuan_disetujui(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'TEST/APP/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan pertama sudah disetujui guru',
            'tujuan'      => 'Bank',
            'jam_keluar'  => 'Jam Ke-1',
            'jam_kembali' => 'Jam Ke-2',
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->siswaUser)
            ->post('/siswa/pengajuan', $this->validStorePayload());

        $response->assertSessionHasErrors('kategori');
        $this->assertCount(1, Dispensasi::where('siswa_id', $this->siswa->id)->get());
    }

    public function test_siswa_tidak_bisa_buat_pengajuan_saat_punya_pengajuan_keluar(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'TEST/OUT/001',
            'kategori'    => 'izin',
            'alasan'      => 'Siswa sedang di luar sekolah',
            'tujuan'      => 'Apotek',
            'jam_keluar'  => 'Jam Ke-1',
            'jam_kembali' => 'Jam Ke-2',
            'status'      => 'keluar',
        ]);

        $response = $this->actingAs($this->siswaUser)
            ->post('/siswa/pengajuan', $this->validStorePayload());

        $response->assertSessionHasErrors('kategori');
        $this->assertCount(1, Dispensasi::where('siswa_id', $this->siswa->id)->get());
    }

    public function test_siswa_bisa_buat_pengajuan_setelah_pengajuan_sebelumnya_selesai(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'TEST/DONE/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan kemarin sudah selesai',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Ke-1',
            'jam_kembali' => 'Jam Ke-2',
            'status'      => 'selesai',
        ]);

        $response = $this->actingAs($this->siswaUser)
            ->post('/siswa/pengajuan', $this->validStorePayload());

        $response->assertRedirect(route('siswa.pengajuan.index'));
        $this->assertCount(2, Dispensasi::where('siswa_id', $this->siswa->id)->get());
    }

    public function test_upload_foto_verifikasi_wajib_saat_buat_pengajuan(): void
    {
        $payload = $this->validStorePayload();
        unset($payload['foto_verifikasi']);

        $response = $this->actingAs($this->siswaUser)
            ->post('/siswa/pengajuan', $payload);

        $response->assertSessionHasErrors('foto_verifikasi');
        $this->assertCount(0, Dispensasi::all());
    }

    public function test_file_qr_dan_foto_dihapus_saat_status_selesai_melalui_qr_scan(): void
    {
        // 1. Setup file fisik pada storage fake
        $qrPath = 'qr-codes/test-qr-scan.svg';
        $fotoVerifPath = 'foto_verifikasi/test-verif-scan.jpg';
        $fotoBuktiPath = 'foto_bukti/test-bukti-scan.jpg';

        Storage::disk('public')->put($qrPath, '<svg>QR TEST</svg>');
        Storage::disk('public')->put($fotoVerifPath, 'IMAGE_DATA_VERIF');
        Storage::disk('public')->put($fotoBuktiPath, 'IMAGE_DATA_BUKTI');

        Storage::disk('public')->assertExists($qrPath);
        Storage::disk('public')->assertExists($fotoVerifPath);
        Storage::disk('public')->assertExists($fotoBuktiPath);

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/QR/LIFECYCLE/001',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian pembersihan file via QR Scan',
            'tujuan'              => 'Puskesmas',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'keluar',
            'qr_code'             => $qrPath,
            'foto_verifikasi'     => $fotoVerifPath,
            'foto_bukti'          => $fotoBuktiPath,
            'qr_token'            => str_repeat('a', 64),
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // 2. Satpam scan QR kembali
        $response = $this->actingAs($this->satpamUser)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $dispensasi->qr_token]),
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        // 3. Verifikasi status menjadi selesai
        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);

        // 4. Verifikasi file fisik telah dihapus dari storage
        Storage::disk('public')->assertMissing($qrPath);
        Storage::disk('public')->assertMissing($fotoVerifPath);
        Storage::disk('public')->assertMissing($fotoBuktiPath);
    }

    public function test_file_qr_dan_foto_dihapus_saat_status_selesai_melalui_satpam_manual(): void
    {
        $qrPath = 'qr-codes/test-qr-manual.png';
        $fotoVerifPath = 'foto_verifikasi/test-verif-manual.jpg';
        $fotoBuktiPath = 'foto_bukti/test-bukti-manual.jpg';

        Storage::disk('public')->put($qrPath, 'QR_PNG_DATA');
        Storage::disk('public')->put($fotoVerifPath, 'IMAGE_DATA_VERIF_MANUAL');
        Storage::disk('public')->put($fotoBuktiPath, 'IMAGE_DATA_BUKTI_MANUAL');

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/MANUAL/LIFECYCLE/002',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian pembersihan file via Satpam Manual',
            'tujuan'              => 'Dinas Pendidikan',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'keluar',
            'qr_code'             => $qrPath,
            'foto_verifikasi'     => $fotoVerifPath,
            'foto_bukti'          => $fotoBuktiPath,
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // Satpam klik konfirmasi kembali manual
        $response = $this->actingAs($this->satpamUser)
            ->post(route('satpam.konfirmasi.kembali', $dispensasi->id));

        $response->assertRedirect();

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);

        // Verifikasi file fisik terhapus
        Storage::disk('public')->assertMissing($qrPath);
        Storage::disk('public')->assertMissing($fotoVerifPath);
        Storage::disk('public')->assertMissing($fotoBuktiPath);
    }

    public function test_record_database_dan_history_tetap_ada_setelah_file_dihapus(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/HISTORY/LIFECYCLE/003',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian keutuhan record database',
            'tujuan'              => 'Rumah',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'keluar',
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        $this->actingAs($this->satpamUser)
            ->post(route('satpam.konfirmasi.kembali', $dispensasi->id));

        // Record database HARUS tetap ada!
        $this->assertDatabaseHas('dispensasi', [
            'id'          => $dispensasi->id,
            'nomor_surat' => 'TEST/HISTORY/LIFECYCLE/003',
            'status'      => 'selesai',
        ]);

        $this->assertNotNull(Dispensasi::find($dispensasi->id));
    }

    public function test_detail_dispensasi_tetap_bisa_dibuka_setelah_file_dihapus(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/VIEW/LIFECYCLE/004',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian halaman detail tetap aman setelah file dihapus',
            'tujuan'              => 'Rumah',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'selesai',
            'foto_verifikasi'     => 'foto_verifikasi/sudah_dihapus.jpg',
            'foto_bukti'          => 'foto_bukti/sudah_dihapus.jpg',
            'qr_code'             => 'qr-codes/sudah_dihapus.svg',
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // File fisiknya sengaja tidak ada (sudah dibersihkan)
        Storage::disk('public')->assertMissing($dispensasi->foto_verifikasi);

        // Siswa membuka detail pengajuan yang sudah selesai
        $response = $this->actingAs($this->siswaUser)
            ->get(route('siswa.pengajuan.show', $dispensasi->id));

        $response->assertOk();
        // Halaman memuat informasi yang informatif dan ramah, bukan broken image
        $response->assertSee('dihapus');
    }

    public function test_siswa_nonaktif_tidak_bisa_akses_halaman_siswa(): void
    {
        // Nonaktifkan siswa
        $this->siswa->update(['status_aktif' => false]);

        $response = $this->actingAs($this->siswaUser)
            ->get(route('siswa.dashboard'));

        // Harus ditolak, di-logout, dan diredirect ke login dengan pesan error
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertFalse(auth()->check());
    }

    public function test_file_tidak_dihapus_ketika_status_masih_keluar(): void
    {
        $qrPath = 'qr_codes/dispensasi_991.svg';
        $fotoVerifPath = 'foto_verifikasi/verif_991.jpg';
        $fotoBuktiPath = 'foto_bukti/bukti_991.jpg';

        Storage::disk('public')->put($qrPath, 'QR_CONTENT');
        Storage::disk('public')->put($fotoVerifPath, 'VERIF_CONTENT');
        Storage::disk('public')->put($fotoBuktiPath, 'BUKTI_CONTENT');

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/GUARD/001',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian guard status keluar',
            'tujuan'              => 'Toko Buku',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'keluar',
            'qr_code'             => $qrPath,
            'foto_verifikasi'     => $fotoVerifPath,
            'foto_bukti'          => $fotoBuktiPath,
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // Panggil cleanup saat status masih 'keluar'
        \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);

        // File HARUS tetap ada karena status belum 'selesai'
        Storage::disk('public')->assertExists($qrPath);
        Storage::disk('public')->assertExists($fotoVerifPath);
        Storage::disk('public')->assertExists($fotoBuktiPath);
    }

    public function test_cleanup_idempotent_dipanggil_berulang_tanpa_exception(): void
    {
        $qrPath = 'qr_codes/dispensasi_992.png';
        $fotoVerifPath = 'foto_verifikasi/verif_992.jpg';
        $fotoBuktiPath = 'foto_bukti/bukti_992.jpg';

        Storage::disk('public')->put($qrPath, 'QR_PNG');
        Storage::disk('public')->put($fotoVerifPath, 'VERIF');
        Storage::disk('public')->put($fotoBuktiPath, 'BUKTI');

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/IDEMPOTENT/002',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian idempotensi cleanup',
            'tujuan'              => 'Rumah',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'selesai',
            'qr_code'             => $qrPath,
            'foto_verifikasi'     => $fotoVerifPath,
            'foto_bukti'          => $fotoBuktiPath,
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // Panggilan pertama: file terhapus
        \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);
        Storage::disk('public')->assertMissing($qrPath);
        Storage::disk('public')->assertMissing($fotoVerifPath);
        Storage::disk('public')->assertMissing($fotoBuktiPath);

        // Panggilan kedua: tidak melempar exception meski file sudah tiada
        \App\Services\DispensasiService::cleanupCompletedDispensasiFiles($dispensasi);
        $this->assertTrue(true);
    }

    public function test_jika_update_status_selesai_gagal_file_tetap_ada(): void
    {
        $qrPath = 'qr_codes/dispensasi_993.svg';
        $fotoVerifPath = 'foto_verifikasi/verif_993.jpg';
        $fotoBuktiPath = 'foto_bukti/bukti_993.jpg';

        Storage::disk('public')->put($qrPath, 'QR_CONTENT');
        Storage::disk('public')->put($fotoVerifPath, 'VERIF_CONTENT');
        Storage::disk('public')->put($fotoBuktiPath, 'BUKTI_CONTENT');

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/FAIL/003',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian rollback file jika update gagal',
            'tujuan'              => 'RS',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'disetujui', // Status BUKAN 'keluar', sehingga konfirmasiKembali akan gagal
            'qr_code'             => $qrPath,
            'foto_verifikasi'     => $fotoVerifPath,
            'foto_bukti'          => $fotoBuktiPath,
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        $dispensasiService = app(\App\Services\DispensasiService::class);

        try {
            $dispensasiService->konfirmasiKembali($dispensasi, $this->satpamUser->id);
            $this->fail('Harus melempar exception karena status bukan keluar.');
        } catch (\InvalidArgumentException $e) {
            // Expected
        }

        // Pastikan status tidak berubah dan file TIDAK terhapus
        $dispensasi->refresh();
        $this->assertNotEquals('selesai', $dispensasi->status);
        Storage::disk('public')->assertExists($qrPath);
        Storage::disk('public')->assertExists($fotoVerifPath);
        Storage::disk('public')->assertExists($fotoBuktiPath);
    }

    public function test_file_dispensasi_lain_tidak_ikut_terhapus(): void
    {
        // Setup Dispensasi A
        $qrA = 'qr_codes/dispensasi_101.svg';
        $fotoVerifA = 'foto_verifikasi/verif_101.jpg';
        $fotoBuktiA = 'foto_bukti/bukti_101.jpg';
        Storage::disk('public')->put($qrA, 'QR_A');
        Storage::disk('public')->put($fotoVerifA, 'VERIF_A');
        Storage::disk('public')->put($fotoBuktiA, 'BUKTI_A');

        $dispensasiA = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/MULTI/001',
            'kategori'            => 'izin',
            'alasan'              => 'Dispensasi A',
            'tujuan'              => 'Tujuan A',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'keluar',
            'qr_code'             => $qrA,
            'foto_verifikasi'     => $fotoVerifA,
            'foto_bukti'          => $fotoBuktiA,
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // Setup Dispensasi B milik siswa lain atau dispensasi lain
        $qrB = 'qr_codes/dispensasi_102.svg';
        $fotoVerifB = 'foto_verifikasi/verif_102.jpg';
        $fotoBuktiB = 'foto_bukti/bukti_102.jpg';
        Storage::disk('public')->put($qrB, 'QR_B');
        Storage::disk('public')->put($fotoVerifB, 'VERIF_B');
        Storage::disk('public')->put($fotoBuktiB, 'BUKTI_B');

        $dispensasiB = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => 'TEST/MULTI/002',
            'kategori'            => 'izin',
            'alasan'              => 'Dispensasi B',
            'tujuan'              => 'Tujuan B',
            'jam_keluar'          => 'Jam Ke-1',
            'jam_kembali'         => 'Jam Ke-2',
            'status'              => 'keluar',
            'qr_code'             => $qrB,
            'foto_verifikasi'     => $fotoVerifB,
            'foto_bukti'          => $fotoBuktiB,
            'batas_waktu_kembali' => now()->addHour(),
        ]);

        // Selesaikan dispensasi A via Satpam manual
        $this->actingAs($this->satpamUser)
            ->post(route('satpam.konfirmasi.kembali', $dispensasiA->id));

        // Verifikasi dispensasi A selesai dan file A terhapus
        $dispensasiA->refresh();
        $this->assertEquals('selesai', $dispensasiA->status);
        Storage::disk('public')->assertMissing($qrA);
        Storage::disk('public')->assertMissing($fotoVerifA);
        Storage::disk('public')->assertMissing($fotoBuktiA);

        // Verifikasi dispensasi B TETAP keluar dan file B TETAP ADA
        $dispensasiB->refresh();
        $this->assertEquals('keluar', $dispensasiB->status);
        Storage::disk('public')->assertExists($qrB);
        Storage::disk('public')->assertExists($fotoVerifB);
        Storage::disk('public')->assertExists($fotoBuktiB);
    }
}
