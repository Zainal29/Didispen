<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\GuruChecklog;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruModuleTest extends TestCase
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

        // Bekukan waktu pada hari Rabu jam 09:00 WIB
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

        $this->guruUser = User::create([
            'name'     => 'Guru Penguji',
            'email'    => 'guru_test@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198501012010011001',
            'password' => Hash::make('password'),
        ]);

        $this->guru = Guru::create([
            'user_id'         => $this->guruUser->id,
            'nip'             => '198501012010011001',
            'nama_lengkap'    => 'Guru Penguji, S.Pd.',
            'mata_pelajaran'  => 'Pemrograman Web',
            'no_telepon'      => '081234567891',
            'status_aktif'    => true,
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Siswa Budi',
            'email'    => 'siswa_budi@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '12345678',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->siswaUser->id,
            'nama_lengkap' => 'Budi Santoso',
            'nis_nip'      => '12345678',
            'kelas_id'     => $this->kelas->id,
            'jurusan_id'   => $this->jurusan->id,
            'no_telepon'   => '081234567890',
            'status_aktif' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function validGuruStorePayload(array $overrides = []): array
    {
        return array_merge([
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Perlombaan LKS tingkat kabupaten di SMK Negeri 2',
            'tujuan'          => 'SMKN 2 Jepara',
            'lokasi'          => 'Jepara',
            'jam_keluar'      => 1,
            'jam_kembali'     => 2,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg', 600, 600),
        ], $overrides);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. LAPORAN
    |--------------------------------------------------------------------------
    */
    public function test_guru_laporan_dapat_dibuka_tanpa_sql_error(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $this->guru->id,
            'nomor_surat' => 'DISP/20260923/001',
            'kategori'    => 'izin',
            'alasan'      => 'Alasan dispensasi untuk laporan test',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->get(route('guru.laporan.index'));

        $response->assertOk();
        $response->assertViewHas('dispensasi');
        $response->assertViewHas('stats');
    }

    public function test_guru_export_pdf_laporan_dapat_dibuat(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $this->guru->id,
            'nomor_surat' => 'DISP/20260923/002',
            'kategori'    => 'izin',
            'alasan'      => 'Alasan dispensasi untuk PDF',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->get(route('guru.laporan.pdf'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_guru_export_excel_laporan_dapat_dibuat(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $this->guru->id,
            'nomor_surat' => 'DISP/20260923/003',
            'kategori'    => 'izin',
            'alasan'      => 'Alasan dispensasi untuk Excel',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->get(route('guru.laporan.excel'));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. MANUAL CREATE & BATAS WAKTU KEMBALI
    |--------------------------------------------------------------------------
    */
    public function test_guru_dapat_membuat_dispensasi_manual_dan_batas_waktu_kembali_tersimpan(): void
    {
        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.store'), $this->validGuruStorePayload());

        $dispensasi = Dispensasi::where('siswa_id', $this->siswa->id)->first();

        $this->assertNotNull($dispensasi);
        $response->assertRedirect(route('guru.pengajuan.show', $dispensasi));

        $this->assertTrue($dispensasi->dibuat_manual_oleh_guru);
        $this->assertEquals('disetujui', $dispensasi->status);
        $this->assertEquals($this->guru->id, $dispensasi->guru_id);
        $this->assertNotNull($dispensasi->approved_at);
        $this->assertNull($dispensasi->rejected_at);
        $this->assertNotNull($dispensasi->batas_waktu_kembali);
        // Jam Pelajaran ke-2 hari Rabu berakhir pada 08:20 (pola Rabu-Kamis 40 menit)
        $this->assertEquals('08:20', $dispensasi->batas_waktu_kembali->format('H:i'));
    }

    /*
    |--------------------------------------------------------------------------
    | 3. DUPLICATE ACTIVE DISPENSASI VALIDATION
    |--------------------------------------------------------------------------
    */
    public function test_guru_manual_create_ditolak_jika_siswa_berstatus_menunggu(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'WAIT/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi aktif yang masih menunggu',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.store'), $this->validGuruStorePayload());

        $response->assertSessionHasErrors('siswa_id');
        $this->assertCount(1, Dispensasi::all());
    }

    public function test_guru_manual_create_ditolak_jika_siswa_berstatus_disetujui(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'APP/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi aktif yang sudah disetujui',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.store'), $this->validGuruStorePayload());

        $response->assertSessionHasErrors('siswa_id');
        $this->assertCount(1, Dispensasi::all());
    }

    public function test_guru_manual_create_ditolak_jika_siswa_berstatus_keluar(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'OUT/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi aktif yang sedang keluar',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'keluar',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.store'), $this->validGuruStorePayload());

        $response->assertSessionHasErrors('siswa_id');
        $this->assertCount(1, Dispensasi::all());
    }

    public function test_guru_manual_create_diizinkan_jika_dispensasi_sebelumnya_selesai(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'DONE/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi kemarin yang sudah selesai',
            'tujuan'      => 'Rumah Sakit',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'selesai',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.store'), $this->validGuruStorePayload());

        $response->assertSessionHasNoErrors();
        $this->assertCount(2, Dispensasi::all());
    }

    public function test_guru_manual_create_diizinkan_jika_dispensasi_sebelumnya_ditolak(): void
    {
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'REJ/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi sebelumnya yang ditolak guru',
            'tujuan'      => 'Toko Buku',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'ditolak',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.store'), $this->validGuruStorePayload());

        $response->assertSessionHasNoErrors();
        $this->assertCount(2, Dispensasi::all());
    }

    /*
    |--------------------------------------------------------------------------
    | 4. APPROVE & REJECT (CONCURRENCY & NOTIFICATIONS)
    |--------------------------------------------------------------------------
    */
    public function test_guru_dapat_approve_dan_notifikasi_dibuat(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'WAIT/APPROVE/001',
            'kategori'    => 'izin',
            'alasan'      => 'Menunggu persetujuan guru piket',
            'tujuan'      => 'Lomba',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-3',
            'status'      => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.approve', $dispensasi), [
                'catatan_admin' => 'Disetujui untuk kegiatan lomba',
            ]);

        $response->assertRedirect(route('guru.pengajuan.index'));

        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);
        $this->assertEquals($this->guru->id, $dispensasi->guru_id);
        $this->assertNotNull($dispensasi->approved_at);
        $this->assertNull($dispensasi->rejected_at);
        $this->assertNotNull($dispensasi->qr_code);

        $notifikasi = Notifikasi::where('user_id', $this->siswaUser->id)->first();
        $this->assertNotNull($notifikasi);
        $this->assertStringContainsString('DISETUJUI', $notifikasi->message);
    }

    public function test_guru_dapat_reject_dan_notifikasi_alasan_dibuat(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'WAIT/REJECT/001',
            'kategori'    => 'izin',
            'alasan'      => 'Menunggu persetujuan untuk ditolak',
            'tujuan'      => 'Beli Kebutuhan Pribadi',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-3',
            'status'      => 'menunggu',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.reject', $dispensasi), [
                'catatan_admin' => 'Alasan tidak mendesak untuk jam pelajaran',
            ]);

        $response->assertRedirect(route('guru.pengajuan.index'));

        $dispensasi->refresh();
        $this->assertEquals('ditolak', $dispensasi->status);
        $this->assertEquals($this->guru->id, $dispensasi->guru_id);
        $this->assertNotNull($dispensasi->rejected_at);
        $this->assertNull($dispensasi->approved_at);
        $this->assertEquals('Alasan tidak mendesak untuk jam pelajaran', $dispensasi->catatan_admin);

        $notifikasi = Notifikasi::where('user_id', $this->siswaUser->id)->first();
        $this->assertNotNull($notifikasi);
        $this->assertStringContainsString('DITOLAK', $notifikasi->message);
        $this->assertStringContainsString('Alasan tidak mendesak untuk jam pelajaran', $notifikasi->message);
    }

    public function test_concurrency_protection_tidak_bisa_reject_dispensasi_yang_sudah_disetujui(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'CONCUR/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengujian concurrent approve reject',
            'tujuan'      => 'Klinik',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.reject', $dispensasi), [
                'catatan_admin' => 'Terlambat mencoba tolak',
            ]);

        $response->assertSessionHas('error', 'Pengajuan ini sudah diproses sebelumnya.');

        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);
    }

    public function test_concurrency_protection_tidak_bisa_approve_dispensasi_yang_sudah_ditolak(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'CONCUR/002',
            'kategori'    => 'izin',
            'alasan'      => 'Pengujian concurrent reject approve',
            'tujuan'      => 'Klinik',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'ditolak',
            'catatan_admin' => 'Sudah ditolak sebelumnya',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.approve', $dispensasi));

        $response->assertSessionHas('error', 'Pengajuan ini sudah diproses sebelumnya.');

        $dispensasi->refresh();
        $this->assertEquals('ditolak', $dispensasi->status);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. GURU INACTIVE
    |--------------------------------------------------------------------------
    */
    public function test_guru_inactive_ditolak_mengakses_area_guru(): void
    {
        $this->guru->update(['status_aktif' => false]);

        $response = $this->actingAs($this->guruUser)
            ->get(route('guru.dashboard'));

        // Dinonaktifkan oleh RoleMiddleware, sesi di-invalidate dan diarahkan ke login
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | 6. GURU PROFILE MISSING
    |--------------------------------------------------------------------------
    */
    public function test_user_role_guru_tanpa_profile_guru_tidak_menghasilkan_http_500(): void
    {
        $userWithoutProfile = User::create([
            'name'     => 'Guru Tanpa Profil',
            'email'    => 'noprofile@sch.id',
            'role'     => 'guru',
            'password' => Hash::make('password'),
        ]);

        // Route yang membutuhkan relasi guru harus abort 403, bukan error 500
        $responseCreate = $this->actingAs($userWithoutProfile)
            ->get(route('guru.pengajuan.create'));
        $responseCreate->assertStatus(403);

        $responseLaporan = $this->actingAs($userWithoutProfile)
            ->get(route('guru.laporan.index'));
        $responseLaporan->assertStatus(403);

        $responseChecklog = $this->actingAs($userWithoutProfile)
            ->get(route('guru.checklog.index'));
        $responseChecklog->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. WARNING BUTTON
    |--------------------------------------------------------------------------
    */
    public function test_warning_menandai_is_warned_dan_warned_at_tanpa_klaim_whatsapp(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => 'WARN/001',
            'kategori'            => 'izin',
            'alasan'              => 'Pengujian peringatan terlambat',
            'tujuan'              => 'Puskesmas',
            'jam_keluar'          => 'Jam Pelajaran ke-1',
            'jam_kembali'         => 'Jam Pelajaran ke-2',
            'batas_waktu_kembali' => now()->subMinutes(15),
            'status'              => 'keluar',
        ]);

        $this->assertTrue($dispensasi->isOverdue());

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.warning.send', $dispensasi));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Siswa ' . $this->siswa->nama_lengkap . ' berhasil ditandai sudah diperingatkan.');

        $dispensasi->refresh();
        $this->assertTrue($dispensasi->is_warned);
        $this->assertNotNull($dispensasi->warned_at);
    }

    /*
    |--------------------------------------------------------------------------
    | 8. CHECKLOG
    |--------------------------------------------------------------------------
    */
    public function test_guru_tidak_bisa_checkout_berulang_saat_masih_ada_log_keluar(): void
    {
        // Checklog pertama
        $response1 = $this->actingAs($this->guruUser)
            ->post(route('guru.checklog.store'), [
                'alasan' => 'Dinas rapat koordinasi MGMP',
                'tujuan' => 'Kantor Cabang Dinas Pendidikan',
                'lokasi' => 'Pati',
            ]);

        $response1->assertRedirect(route('guru.checklog.index'));
        $this->assertCount(1, GuruChecklog::all());
        $this->assertEquals('keluar', GuruChecklog::first()->status);

        // Percobaan checkout kedua saat status masih keluar harus ditolak
        $response2 = $this->actingAs($this->guruUser)
            ->post(route('guru.checklog.store'), [
                'alasan' => 'Checkout kedua yang seharusnya ditolak',
                'tujuan' => 'Bank Jateng',
            ]);

        $response2->assertSessionHas('error');
        $this->assertCount(1, GuruChecklog::all());
    }

    public function test_guru_checkin_tanpa_log_keluar_aktif_ditolak(): void
    {
        $logSelesai = GuruChecklog::create([
            'guru_id'     => $this->guru->id,
            'alasan'      => 'Sudah selesai kemarin',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => now()->subDay(),
            'jam_kembali' => now()->subDay()->addHour(),
            'status'      => 'selesai',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->post(route('guru.checklog.checkin', $logSelesai));

        $response->assertSessionHas('error');
        $logSelesai->refresh();
        $this->assertEquals('selesai', $logSelesai->status);
    }

    /*
    |--------------------------------------------------------------------------
    | 9. DETAIL VIEW UNTUK SEMUA STATUS (TIDAK ADA ERROR BLADE)
    |--------------------------------------------------------------------------
    */
    public function test_guru_dapat_melihat_detail_pengajuan_semua_status(): void
    {
        $statuses = ['menunggu', 'disetujui', 'keluar', 'ditolak', 'selesai'];

        foreach ($statuses as $status) {
            $dispensasi = Dispensasi::create([
                'siswa_id'            => $this->siswa->id,
                'guru_id'             => $this->guru->id,
                'nomor_surat'         => 'TEST/' . strtoupper($status) . '/001',
                'kategori'            => 'izin',
                'alasan'              => 'Uji coba status ' . $status,
                'tujuan'              => 'Rumah',
                'jam_keluar'          => 'Jam Pelajaran ke-1',
                'jam_kembali'         => 'Jam Pelajaran ke-2',
                'batas_waktu_kembali' => now()->addHour(),
                'status'              => $status,
                'catatan_admin'       => $status === 'ditolak' ? 'Ditolak untuk tes' : null,
            ]);

            $response = $this->actingAs($this->guruUser)
                ->get(route('guru.pengajuan.show', $dispensasi));

            $response->assertOk();
            $response->assertSee(ucfirst($status));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 10. DASHBOARD STATISTIK TERLAMBAT
    |--------------------------------------------------------------------------
    */
    public function test_guru_dashboard_menghitung_siswa_terlambat_dengan_benar(): void
    {
        // Siswa sedang keluar dan terlambat
        Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => 'LATE/001',
            'kategori'            => 'izin',
            'alasan'              => 'Siswa keluar dan belum kembali melebihi batas waktu',
            'tujuan'              => 'Klinik',
            'jam_keluar'          => 'Jam Pelajaran ke-1',
            'jam_kembali'         => 'Jam Pelajaran ke-2',
            'batas_waktu_kembali' => now()->subMinutes(30),
            'status'              => 'keluar',
        ]);

        $response = $this->actingAs($this->guruUser)
            ->get(route('guru.dashboard', ['filter' => 'terlambat']));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return isset($stats['terlambat']) && $stats['terlambat'] === 1;
        });
        $response->assertSee('Terlambat (1)');
    }

    /*
    |--------------------------------------------------------------------------
    | 11. AUDIT LOG UNTUK APPROVE DAN REJECT GURU
    |--------------------------------------------------------------------------
    */
    public function test_guru_approve_dan_reject_mencatat_audit_log(): void
    {
        $dispensasiApprove = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'AUDIT/APP/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengujian audit log approve',
            'tujuan'      => 'Lomba',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.approve', $dispensasiApprove));

        $this->assertDatabaseHas('audit_logs', [
            'user_id'    => $this->guruUser->id,
            'action'     => 'approve',
            'table_name' => 'dispensasi',
            'record_id'  => $dispensasiApprove->id,
        ]);

        $dispensasiReject = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'AUDIT/REJ/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengujian audit log reject',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.reject', $dispensasiReject), [
            'catatan_admin' => 'Alasan penolakan audit log test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id'    => $this->guruUser->id,
            'action'     => 'reject',
            'table_name' => 'dispensasi',
            'record_id'  => $dispensasiReject->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 12. IMMUTABILITY OF APPROVED_AT PADA EVENT SELANJUTNYA
    |--------------------------------------------------------------------------
    */
    public function test_approved_at_tetap_sama_saat_updated_at_berubah_oleh_event_berikutnya(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'IMMUTABLE/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengujian timestamp tidak menggunakan updated_at',
            'tujuan'      => 'Rumah',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        $t1 = Carbon::parse('2026-09-23 09:15:00', 'Asia/Jakarta');
        Carbon::setTestNow($t1);

        $this->actingAs($this->guruUser)
            ->post(route('guru.pengajuan.approve', $dispensasi));

        $dispensasi->refresh();
        $this->assertEquals($t1->toDateTimeString(), $dispensasi->approved_at->toDateTimeString());
        $initialApprovedAt = $dispensasi->approved_at;

        // Waktu maju 1 jam: Satpam memproses keluar
        $t2 = Carbon::parse('2026-09-23 10:15:00', 'Asia/Jakarta');
        Carbon::setTestNow($t2);

        $dispensasi->update([
            'status' => 'keluar',
            'waktu_keluar_aktual' => now(),
        ]);

        $dispensasi->refresh();
        // updated_at berubah menjadi T2
        $this->assertEquals($t2->toDateTimeString(), $dispensasi->updated_at->toDateTimeString());
        // Namun approved_at TETAP T1, tidak berubah mengikuti updated_at
        $this->assertEquals($initialApprovedAt->toDateTimeString(), $dispensasi->approved_at->toDateTimeString());
        $this->assertNotEquals($dispensasi->updated_at->toDateTimeString(), $dispensasi->approved_at->toDateTimeString());
    }

    /*
    |--------------------------------------------------------------------------
    | 13. SHARED QUEUE (SEMUA GURU BISA MELIHAT & MEMPROSES PENGAJUAN)
    |--------------------------------------------------------------------------
    */
    public function test_shared_queue_semua_guru_dapat_melihat_dan_memproses_pengajuan_menunggu(): void
    {
        // Buat guru kedua
        $guruUser2 = User::create([
            'name'     => 'Guru Kedua',
            'email'    => 'guru2@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198802022012022002',
            'password' => Hash::make('password'),
        ]);

        $guru2 = Guru::create([
            'user_id'        => $guruUser2->id,
            'nip'            => '198802022012022002',
            'nama_lengkap'   => 'Guru Kedua, M.Pd.',
            'mata_pelajaran' => 'Bahasa Indonesia',
            'status_aktif'   => true,
        ]);

        $dispensasi = Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'nomor_surat' => 'SHARED/001',
            'kategori'    => 'izin',
            'alasan'      => 'Pengajuan siswa yang dapat dilihat semua guru',
            'tujuan'      => 'Klinik',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'menunggu',
        ]);

        // Guru kedua dapat melihat daftar (shared queue)
        $responseIndex = $this->actingAs($guruUser2)
            ->get(route('guru.pengajuan.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('SHARED/001');

        // Guru kedua dapat melihat detail
        $responseShow = $this->actingAs($guruUser2)
            ->get(route('guru.pengajuan.show', $dispensasi));
        $responseShow->assertOk();

        // Guru kedua dapat menyetujui pengajuan tersebut
        $responseApprove = $this->actingAs($guruUser2)
            ->post(route('guru.pengajuan.approve', $dispensasi));
        $responseApprove->assertRedirect(route('guru.pengajuan.index'));

        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);
        $this->assertEquals($guru2->id, $dispensasi->guru_id);
    }

    /*
    |--------------------------------------------------------------------------
    | 14. LAPORAN GURU TETAP PER-GURU
    |--------------------------------------------------------------------------
    */
    public function test_guru_laporan_hanya_menampilkan_dispensasi_milik_guru_yang_login(): void
    {
        $guruUser2 = User::create([
            'name'     => 'Guru Kedua Laporan',
            'email'    => 'guru2_lap@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198903032013032003',
            'password' => Hash::make('password'),
        ]);

        $guru2 = Guru::create([
            'user_id'        => $guruUser2->id,
            'nip'            => '198903032013032003',
            'nama_lengkap'   => 'Guru Kedua Laporan, M.Pd.',
            'mata_pelajaran' => 'Matematika',
            'status_aktif'   => true,
        ]);

        // Dispensasi diproses guru 1
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $this->guru->id,
            'nomor_surat' => 'LAP/GURU1/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi diproses guru 1',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
            'approved_at' => now(),
        ]);

        // Dispensasi diproses guru 2
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $guru2->id,
            'nomor_surat' => 'LAP/GURU2/002',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi diproses guru 2',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
            'approved_at' => now(),
        ]);

        // Guru 1 hanya melihat LAP/GURU1/001
        $response1 = $this->actingAs($this->guruUser)->get(route('guru.laporan.index'));
        $response1->assertOk();
        $response1->assertSee('LAP/GURU1/001');
        $response1->assertDontSee('LAP/GURU2/002');
    }

    public function test_guru_lain_hanya_melihat_laporan_miliknya_sendiri(): void
    {
        $guruUser2 = User::create([
            'name'     => 'Guru Kedua Laporan',
            'email'    => 'guru2_lap@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198903032013032003',
            'password' => Hash::make('password'),
        ]);

        $guru2 = Guru::create([
            'user_id'        => $guruUser2->id,
            'nip'            => '198903032013032003',
            'nama_lengkap'   => 'Guru Kedua Laporan, M.Pd.',
            'mata_pelajaran' => 'Matematika',
            'status_aktif'   => true,
        ]);

        // Dispensasi diproses guru 1
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $this->guru->id,
            'nomor_surat' => 'LAP/GURU1/001',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi diproses guru 1',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
            'approved_at' => now(),
        ]);

        // Dispensasi diproses guru 2
        Dispensasi::create([
            'siswa_id'    => $this->siswa->id,
            'guru_id'     => $guru2->id,
            'nomor_surat' => 'LAP/GURU2/002',
            'kategori'    => 'izin',
            'alasan'      => 'Dispensasi diproses guru 2',
            'tujuan'      => 'Kantor',
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'status'      => 'disetujui',
            'approved_at' => now(),
        ]);

        // Guru 2 hanya melihat LAP/GURU2/002
        $response2 = $this->actingAs($guruUser2)->get(route('guru.laporan.index'));
        $response2->assertOk();
        $response2->assertSee('LAP/GURU2/002');
        $response2->assertDontSee('LAP/GURU1/001');
    }
}
