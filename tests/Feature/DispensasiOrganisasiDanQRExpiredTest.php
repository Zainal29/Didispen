<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use App\Services\DispensasiService;
use App\Services\QRScanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DispensasiOrganisasiDanQRExpiredTest extends TestCase
{
    use RefreshDatabase;

    protected User $userKetua;
    protected Siswa $siswaKetua;
    protected User $userAnggota1;
    protected Siswa $siswaAnggota1;
    protected User $userAnggota2;
    protected Siswa $siswaAnggota2;
    protected User $userSatpam;
    protected Jurusan $jurusan;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Carbon::setTestNow(Carbon::parse('2026-10-12 08:00:00', 'Asia/Jakarta')); // Senin

        Setting::set('dispensasi_start_time', '07:00');
        Setting::set('dispensasi_end_time', '15:30');
        Setting::set('dispensasi_end_time_friday', '14:00');
        Setting::set('dispensasi_days', '1,2,3,4,5');

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kode_jurusan' => 'RPL',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $this->userKetua = User::create([
            'name' => 'Ketua OSIS',
            'email' => 'ketua@didispen.sch.id',
            'role' => 'siswa',
            'nis_nip' => '10001',
            'password' => Hash::make('password'),
        ]);

        $this->siswaKetua = Siswa::create([
            'user_id' => $this->userKetua->id,
            'kelas_id' => $this->kelas->id,
            'jurusan_id' => $this->jurusan->id,
            'nama_lengkap' => 'Ketua OSIS',
            'nis_nip' => '10001',
            'no_telepon' => '081234567890',
            'status_aktif' => true,
        ]);

        $this->userAnggota1 = User::create([
            'name' => 'Anggota 1',
            'email' => 'anggota1@didispen.sch.id',
            'role' => 'siswa',
            'nis_nip' => '10002',
            'password' => Hash::make('password'),
        ]);

        $this->siswaAnggota1 = Siswa::create([
            'user_id' => $this->userAnggota1->id,
            'kelas_id' => $this->kelas->id,
            'jurusan_id' => $this->jurusan->id,
            'nama_lengkap' => 'Anggota 1',
            'nis_nip' => '10002',
            'no_telepon' => '081234567891',
            'status_aktif' => true,
        ]);

        $this->userAnggota2 = User::create([
            'name' => 'Anggota 2',
            'email' => 'anggota2@didispen.sch.id',
            'role' => 'siswa',
            'nis_nip' => '10003',
            'password' => Hash::make('password'),
        ]);

        $this->siswaAnggota2 = Siswa::create([
            'user_id' => $this->userAnggota2->id,
            'kelas_id' => $this->kelas->id,
            'jurusan_id' => $this->jurusan->id,
            'nama_lengkap' => 'Anggota 2',
            'nis_nip' => '10003',
            'no_telepon' => '081234567892',
            'status_aktif' => true,
        ]);

        $this->userSatpam = User::create([
            'name' => 'Satpam Pos',
            'email' => 'satpam@didispen.sch.id',
            'role' => 'satpam',
            'nis_nip' => '99999',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_siswa_bisa_membuat_pengajuan_organisasi_dengan_anggota_unlimited(): void
    {
        $response = $this->actingAs($this->userKetua)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'OSIS SMKN 1 Bangsri',
            'tanggal_izin' => '2026-10-12',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mempersiapkan acara peringatan hari besar nasional di aula.',
            'tujuan' => 'Aula Sekolah',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'jam_kembali' => '11:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
            'anggota_ids' => [$this->siswaAnggota1->id, $this->siswaAnggota2->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('siswa.pengajuan.index'));

        $dispensasi = Dispensasi::where('siswa_id', $this->siswaKetua->id)->first();
        $this->assertNotNull($dispensasi);
        $this->assertEquals('organisasi', $dispensasi->tipe_pengajuan);
        $this->assertEquals('OSIS SMKN 1 Bangsri', $dispensasi->nama_organisasi);
        $this->assertCount(2, $dispensasi->anggotaSiswa);
        $this->assertTrue($dispensasi->anggotaSiswa->contains($this->siswaAnggota1->id));
        $this->assertTrue($dispensasi->anggotaSiswa->contains($this->siswaAnggota2->id));

        // Anggota bisa melihat di detail pengajuan
        $this->flushSession();
        $showResp = $this->actingAs($this->userAnggota1)->get(route('siswa.pengajuan.show', $dispensasi));
        $showResp->assertOk()
            ->assertSee('OSIS SMKN 1 Bangsri')
            ->assertSee('Anggota 1');
    }

    public function test_siswa_bisa_mengajukan_dispensasi_sampai_pulang_tanpa_kena_overdue(): void
    {
        $response = $this->actingAs($this->userKetua)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'individu',
            'kategori' => 'keperluan_sekolah',
            'sampai_pulang' => '1',
            'alasan' => 'Mengikuti perlombaan futsal antar sekolah sampai selesai kbm.',
            'tujuan' => 'GOR Bangsri',
            'no_telepon' => '081234567890',
            'jam_keluar' => '10:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertSessionHasNoErrors();

        $dispensasi = Dispensasi::where('siswa_id', $this->siswaKetua->id)->first();
        $this->assertNotNull($dispensasi);
        $this->assertTrue((bool)$dispensasi->sampai_pulang);
        $this->assertEquals('Sampai Pulang Sekolah', $dispensasi->jam_kembali);

        // Simulasi disetujui dan siswa keluar
        $dispensasi->update([
            'status' => 'keluar',
            'waktu_keluar_aktual' => now('Asia/Jakarta'),
            'qr_token' => 'VALID_TOKEN_123',
        ]);

        // Maju waktu hingga lewat jam KBM (16:00)
        Carbon::setTestNow(Carbon::parse('2026-10-12 16:00:00', 'Asia/Jakarta'));

        // Dispensasi sampai_pulang TIDAK BOLEH dianggap overdue
        $this->assertFalse($dispensasi->isOverdue());

        // Jalankan auto-complete kbm
        Artisan::call('dispensasi:auto-complete');

        $dispensasi->refresh();
        $this->assertEquals('selesai', $dispensasi->status);
    }

    public function test_qr_code_expired_jika_discan_di_hari_berbeda_dari_tanggal_izin(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->siswaKetua->id,
            'nomor_surat' => 'DISP-2026-TEST1',
            'kategori' => 'izin',
            'alasan' => 'Urusan penting',
            'tujuan' => 'Rumah',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'jam_kembali' => '11:00',
            'status' => 'disetujui',
            'tanggal_izin' => '2026-10-12',
            'qr_token' => 'TOKEN_EXP_TEST',
            'foto_verifikasi' => 'dummy.jpg',
        ]);

        $qrService = app(QRScanService::class);

        // Scan keluar di hari yang sama (12 Oktober) -> Sah
        Carbon::setTestNow(Carbon::parse('2026-10-12 09:00:00', 'Asia/Jakarta'));
        $resultHariIni = $qrService->processKeluar($dispensasi, $this->userSatpam->id);
        $this->assertTrue($resultHariIni['success']);

        // Reset status
        $dispensasi->update(['status' => 'disetujui', 'waktu_keluar_aktual' => null]);

        // Siswa mencoba scan foto QR di hari esoknya (13 Oktober) -> GAGAL KADALUARSA
        Carbon::setTestNow(Carbon::parse('2026-10-13 08:00:00', 'Asia/Jakarta'));
        $resultBesok = $qrService->processKeluar($dispensasi, $this->userSatpam->id);
        $this->assertFalse($resultBesok['success']);
        $this->assertStringContainsString('KADALUARSA', $resultBesok['message']);
    }

    public function test_dispensasi_gantung_otomatis_kadaluarsa_jika_lewat_hari(): void
    {
        // Dispensasi dibuat kemarin dan statusnya menunggu / disetujui tanpa checkout
        $dispensasiKemarin = Dispensasi::create([
            'siswa_id' => $this->siswaKetua->id,
            'nomor_surat' => 'DISP-2026-TEST2',
            'kategori' => 'izin',
            'alasan' => 'Urusan kemarin',
            'tujuan' => 'Rumah',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'jam_kembali' => '11:00',
            'status' => 'menunggu',
            'tanggal_izin' => '2026-10-11',
            'foto_verifikasi' => 'dummy.jpg',
            'created_at' => Carbon::parse('2026-10-11 08:00:00', 'Asia/Jakarta'),
        ]);

        // Hari ini (12 Oktober)
        Carbon::setTestNow(Carbon::parse('2026-10-12 08:00:00', 'Asia/Jakarta'));

        $expiredCount = DispensasiService::autoExpireOverdueDispensasi();

        $this->assertGreaterThanOrEqual(1, $expiredCount);
        $dispensasiKemarin->refresh();
        $this->assertEquals('kadaluarsa', $dispensasiKemarin->status);
    }

    public function test_anggota_rombongan_bisa_mencetak_dokumen_pdf_dengan_daftar_nama_anggota(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->siswaKetua->id,
            'nomor_surat' => 'DISP-2026-PDF1',
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'PMR WIRA',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Kegiatan donor darah PMI',
            'tujuan' => 'PMI Cabang',
            'no_telepon' => '081234567890',
            'jam_keluar' => '08:00',
            'jam_kembali' => 'Sampai Pulang Sekolah',
            'sampai_pulang' => true,
            'status' => 'disetujui',
            'tanggal_izin' => '2026-10-12',
            'foto_verifikasi' => 'dummy.jpg',
        ]);
        $dispensasi->anggotaSiswa()->sync([$this->siswaAnggota1->id, $this->siswaAnggota2->id]);

        // Anggota 1 mencetak surat dispensasi
        $this->flushSession();
        $response = $this->actingAs($this->userAnggota1)->get(route('siswa.cetak', $dispensasi));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_satpam_bisa_memindai_dan_melihat_metadata_rombongan_organisasi(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->siswaKetua->id,
            'nomor_surat' => 'DISP-2026-SCAN1',
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'Pramuka Ambalan',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Giat persami kwarran',
            'tujuan' => 'Bumi Perkemahan',
            'no_telepon' => '081234567890',
            'jam_keluar' => '10:00',
            'jam_kembali' => 'Sampai Pulang Sekolah',
            'sampai_pulang' => true,
            'status' => 'disetujui',
            'tanggal_izin' => '2026-10-12',
            'qr_token' => 'QR_PRAMUKA_123456789',
            'foto_verifikasi' => 'dummy.jpg',
        ]);
        $dispensasi->anggotaSiswa()->sync([$this->siswaAnggota1->id]);

        $this->flushSession();
        $response = $this->actingAs($this->userSatpam)->post(route('satpam.scan.verify'), [
            'qr_data' => 'QR_PRAMUKA_123456789',
            'action' => 'check',
        ]);

        $response->assertOk();
        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertEquals('organisasi', $json['data']['tipe_pengajuan']);
        $this->assertEquals('Pramuka Ambalan', $json['data']['nama_organisasi']);
        $this->assertTrue($json['data']['is_sampai_pulang']);
        $this->assertCount(1, $json['data']['anggota']);
        $this->assertEquals(2, $json['data']['total_rombongan']);
        $this->assertEquals('ANGGOTA 1', $json['data']['anggota'][0]['nama_lengkap']);
    }
}
