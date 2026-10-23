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
use App\Services\DispensasiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DispensasiKadaluarsaAlertDanAutoExpireTest extends TestCase
{
    use RefreshDatabase;

    private User $guruUser;
    private Guru $guru;
    private User $siswaUser;
    private Siswa $siswa;
    private User $satpamUser;
    private Kelas $kelas;
    private Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

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
            'no_telepon'     => '081234567890',
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

        $this->satpamUser = User::create([
            'name'     => 'Satpam Testing',
            'email'    => 'satpam@sch.id',
            'role'     => 'satpam',
            'password' => Hash::make('password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 1. Dispensasi yang disetujui tapi siswa tidak kunjung scan keluar,
     * otomatis menjadi KADALUARSA begitu jam KBM hari itu berakhir.
     */
    public function test_dispensasi_disetujui_tanpa_scan_otomatis_kadaluarsa_saat_kbm_usai(): void
    {
        // Set waktu pagi hari Rabu jam 09:00 WIB (KBM belum selesai, selesai 15:10 WIB)
        Carbon::setTestNow(Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'     => $this->siswa->id,
            'guru_id'      => $this->guru->id,
            'nomor_surat'  => Dispensasi::generateNomorSurat(),
            'kategori'     => 'izin',
            'alasan'       => 'Urusan keluarga mendesak',
            'tujuan'       => 'Rumah',
            'tanggal_izin' => '2026-10-14',
            'jam_keluar'   => '09:30',
            'jam_kembali'  => '11:00',
            'status'       => 'disetujui',
            'qr_token'     => 'token_test_disetujui_123',
        ]);

        // Saat jam 09:00 WIB, jalankan autoExpire -> status tetap disetujui
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);

        // Majukan waktu ke pukul 15:30 WIB di hari yang sama (KBM Rabu berakhir pukul 15:10 WIB)
        Carbon::setTestNow(Carbon::parse('2026-10-14 15:30:00', 'Asia/Jakarta'));

        // Jalankan auto-expire
        $affected = DispensasiService::autoExpireOverdueDispensasi();
        $this->assertGreaterThanOrEqual(1, $affected);

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
        $this->assertStringContainsString('scan keluar', $dispensasi->catatan_admin);

        // Periksa badge status
        $badge = $dispensasi->status_badge;
        $this->assertEquals('Kadaluarsa (Tidak Scan Keluar)', $badge['text']);
    }

    /**
     * 2. Dispensasi yang masih menunggu persetujuan dan tidak kunjung disetujui,
     * otomatis menjadi KADALUARSA begitu jam KBM hari itu usai.
     */
    public function test_dispensasi_menunggu_otomatis_kadaluarsa_saat_kbm_usai(): void
    {
        // Set waktu pagi hari Rabu jam 08:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-14 08:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'     => $this->siswa->id,
            'nomor_surat'  => Dispensasi::generateNomorSurat(),
            'kategori'     => 'izin',
            'alasan'       => 'Sakit gigi',
            'tujuan'       => 'Puskesmas',
            'tanggal_izin' => '2026-10-14',
            'jam_keluar'   => '08:30',
            'jam_kembali'  => '10:00',
            'status'       => 'menunggu',
        ]);

        // Saat KBM belum selesai, status tetap menunggu
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);

        // Majukan waktu ke setelah jam KBM berakhir (15:20 WIB)
        Carbon::setTestNow(Carbon::parse('2026-10-14 15:20:00', 'Asia/Jakarta'));

        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
        $this->assertStringContainsString('tidak disetujui Guru Piket', $dispensasi->catatan_admin);
    }

    /**
     * 3. Sebelum jam KBM berakhir, alert peringatan akan segera kadaluarsa
     * tampil di halaman detail siswa dan guru.
     */
    public function test_alert_segera_kadaluarsa_tampil_di_halaman_siswa_dan_guru(): void
    {
        // Set waktu pagi hari jam 10:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-14 10:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'     => $this->siswa->id,
            'nomor_surat'  => Dispensasi::generateNomorSurat(),
            'kategori'     => 'izin',
            'alasan'       => 'Menunggu persetujuan',
            'tujuan'       => 'Keperluan keluarga',
            'tanggal_izin' => '2026-10-14',
            'jam_keluar'   => '10:30',
            'jam_kembali'  => '12:00',
            'status'       => 'menunggu',
        ]);

        // Cek halaman siswa: ada alert akan segera kadaluarsa
        $responseSiswa = $this->actingAs($this->siswaUser)
            ->get(route('siswa.pengajuan.show', $dispensasi));

        $responseSiswa->assertOk();
        $responseSiswa->assertSee('Pengajuan Ini Akan Segera Kadaluarsa');
        $responseSiswa->assertSee('Kadaluarsa:');

        // Cek halaman guru: ada peringatan agar guru piket segera memutuskan
        $this->flushSession();
        $responseGuru = $this->actingAs($this->guruUser)
            ->get(route('guru.pengajuan.show', $dispensasi));

        $responseGuru->assertOk();
        $responseGuru->assertSee('Perhatian: Pengajuan Ini Akan Segera Kadaluarsa');
        $responseGuru->assertSee('Setujui');
    }

    /**
     * 4. Jika satpam mencoba scan QR setelah jam KBM berakhir, sistem menolak
     * dan mengubah status menjadi kadaluarsa.
     */
    public function test_satpam_menolak_scan_keluar_setelah_jam_kbm_usai(): void
    {
        // Awalnya disetujui di jam 10:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-14 10:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'     => $this->siswa->id,
            'guru_id'      => $this->guru->id,
            'nomor_surat'  => Dispensasi::generateNomorSurat(),
            'kategori'     => 'izin',
            'alasan'       => 'Izin urusan bank',
            'tujuan'       => 'Bank',
            'tanggal_izin' => '2026-10-14',
            'jam_keluar'   => '10:30',
            'jam_kembali'  => '12:00',
            'status'       => 'disetujui',
            'qr_token'     => 'token_satpam_kadaluarsa_test',
        ]);

        // Siswa baru datang ke gerbang satpam pukul 16:00 WIB (KBM sudah selesai)
        Carbon::setTestNow(Carbon::parse('2026-10-14 16:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->satpamUser)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $dispensasi->qr_token]),
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('KADALUARSA', $response->json('message'));

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
    }

    /**
     * 5. Dispensasi yang disetujui tapi jam kembali terlewati sebelum siswa scan keluar,
     * otomatis menjadi KADALUARSA di tengah hari (meski jam KBM belum selesai).
     */
    public function test_dispensasi_disetujui_tanpa_scan_otomatis_kadaluarsa_saat_jam_kembali_terlewati_meski_kbm_belum_selesai(): void
    {
        // Pagi hari jam 09:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => Dispensasi::generateNomorSurat(),
            'kategori'            => 'izin',
            'alasan'              => 'Keperluan fotokopi buku',
            'tujuan'              => 'Fotokopi depan',
            'tanggal_izin'        => '2026-10-14',
            'jam_keluar'          => '09:00',
            'jam_kembali'         => '09:03',
            'batas_waktu_kembali' => Carbon::parse('2026-10-14 09:03:00', 'Asia/Jakarta'),
            'status'              => 'disetujui',
            'qr_token'            => 'token_test_realtime_disetujui',
        ]);

        // Pada 09:02 WIB: belum lewat batas kembali -> tetap disetujui
        Carbon::setTestNow(Carbon::parse('2026-10-14 09:02:00', 'Asia/Jakarta'));
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('disetujui', $dispensasi->status);

        // Majukan ke 09:05 WIB (KBM Rabu masih aktif sampai 15:10 WIB)
        Carbon::setTestNow(Carbon::parse('2026-10-14 09:05:00', 'Asia/Jakarta'));
        $affected = DispensasiService::autoExpireOverdueDispensasi();
        $this->assertGreaterThanOrEqual(1, $affected);

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
        $this->assertStringContainsString('scan keluar', $dispensasi->catatan_admin);
        $this->assertStringContainsString('09:03 WIB', $dispensasi->catatan_admin);
        $this->assertEquals('Kadaluarsa (Tidak Scan Keluar)', $dispensasi->status_badge['text']);
    }

    /**
     * 6. Jika batas kembali terlewati sebelum siswa scan keluar:
     * - Halaman detail satpam menampilkan banner kadaluarsa dan menghapus tombol scan
     * - Request verify satpam ditolak dengan 422
     */
    public function test_satpam_menolak_scan_keluar_ketika_jam_kembali_terlewati_dan_halaman_detail_menampilkan_banner_kadaluarsa(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 09:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'guru_id'             => $this->guru->id,
            'nomor_surat'         => Dispensasi::generateNomorSurat(),
            'kategori'            => 'izin',
            'alasan'              => 'Beli alat tulis',
            'tujuan'              => 'Toko Buku',
            'tanggal_izin'        => '2026-10-14',
            'jam_keluar'          => '09:00',
            'jam_kembali'         => '09:03',
            'batas_waktu_kembali' => Carbon::parse('2026-10-14 09:03:00', 'Asia/Jakarta'),
            'status'              => 'disetujui',
            'qr_token'            => 'token_satpam_midday_test',
        ]);

        // Sekarang pukul 09:07 WIB
        Carbon::setTestNow(Carbon::parse('2026-10-14 09:07:00', 'Asia/Jakarta'));

        // Akses halaman detail satpam
        $responseDetail = $this->actingAs($this->satpamUser)
            ->get(route('satpam.dispensasi.detail', $dispensasi));

        $responseDetail->assertOk();
        $responseDetail->assertSee('Dispensasi Kadaluarsa');
        $responseDetail->assertSee('Arahkan siswa kembali ke kelas / Guru Piket');
        $responseDetail->assertDontSee('Scan QR Code untuk Keluar');

        // Satpam scan QR di pos gerbang
        $responseVerify = $this->actingAs($this->satpamUser)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $dispensasi->qr_token]),
            ]);

        $responseVerify->assertStatus(422);
        $responseVerify->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('KADALUARSA', $responseVerify->json('message'));

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
    }

    /**
     * 7. Dispensasi status 'menunggu' menggunakan kombinasi cerdas (MAX antara jam pembuatan + 20m dan jam keluar + 20m).
     * Jika dibuat 12:45 untuk jam keluar 12:50, batas kadaluarsa adalah 13:10 WIB.
     */
    public function test_dispensasi_menunggu_otomatis_kadaluarsa_setelah_20_menit(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-19 12:45:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => Dispensasi::generateNomorSurat(),
            'kategori'            => 'izin',
            'alasan'              => 'Fotokopi materi ujian',
            'tujuan'              => 'Fotokopi depan',
            'tanggal_izin'        => '2026-10-19',
            'jam_keluar'          => '12:50',
            'jam_kembali'         => '14:00',
            'batas_waktu_kembali' => Carbon::parse('2026-10-19 14:00:00', 'Asia/Jakarta'),
            'status'              => 'menunggu',
        ]);

        // Pada 13:06 (karena jam_keluar 12:50 + 20m = 13:10, pada 13:06 masih aktif) -> tetap menunggu
        Carbon::setTestNow(Carbon::parse('2026-10-19 13:06:00', 'Asia/Jakarta'));
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);

        // Pada 13:11 (melewati 13:10 WIB) -> harus otomatis kadaluarsa
        Carbon::setTestNow(Carbon::parse('2026-10-19 13:11:00', 'Asia/Jakarta'));
        $affected = DispensasiService::autoExpireOverdueDispensasi();
        $this->assertGreaterThanOrEqual(1, $affected);

        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
        $this->assertStringContainsString('13:10 WIB', $dispensasi->catatan_admin);
        $this->assertStringContainsString('tidak disetujui Guru Piket', $dispensasi->catatan_admin);
    }

    /**
     * 8. Pengajuan lebih awal (advance) tidak kadaluarsa prematur.
     * Contoh: Dibuat jam 08:00 untuk jam keluar 09:30.
     * Batas kadaluarsa = 09:30 + 20 menit = 09:50 WIB.
     * Pada jam 08:25 (lewat 25 menit dari created_at) TETAP aktif karena belum jam 09:50.
     */
    public function test_dispensasi_menunggu_diajukan_lebih_awal_tidak_kadaluarsa_prematur(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-19 08:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id'            => $this->siswa->id,
            'nomor_surat'         => Dispensasi::generateNomorSurat(),
            'kategori'            => 'izin',
            'alasan'              => 'Lomba debat bahasa inggris',
            'tujuan'              => 'Gedung Kesenian',
            'tanggal_izin'        => '2026-10-19',
            'jam_keluar'          => '09:30',
            'jam_kembali'         => '12:00',
            'batas_waktu_kembali' => Carbon::parse('2026-10-19 12:00:00', 'Asia/Jakarta'),
            'status'              => 'menunggu',
        ]);

        $this->assertEquals('09:50 WIB', $dispensasi->getBatasWaktuExpiredText());

        // Pada 08:25 (lewat 25 menit sejak dibuat): HARUS TETAP MENUNGGU
        Carbon::setTestNow(Carbon::parse('2026-10-19 08:25:00', 'Asia/Jakarta'));
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('menunggu', $dispensasi->status);

        // Pada 09:55 (melewati 09:50 WIB): Baru otomatis kadaluarsa
        Carbon::setTestNow(Carbon::parse('2026-10-19 09:55:00', 'Asia/Jakarta'));
        DispensasiService::autoExpireOverdueDispensasi();
        $dispensasi->refresh();
        $this->assertEquals('kadaluarsa', $dispensasi->status);
        $this->assertStringContainsString('09:50 WIB', $dispensasi->catatan_admin);
    }
}
