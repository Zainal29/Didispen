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
}
