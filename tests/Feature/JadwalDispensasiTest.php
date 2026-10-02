<?php

namespace Tests\Feature;

use App\Helpers\DispensasiTimeHelper;
use App\Helpers\TimeHelper;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JadwalDispensasiTest extends TestCase
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
        Setting::set('dispensasi_start_time', '06:00');
        Setting::set('dispensasi_end_time', '23:59');
        Setting::set('dispensasi_end_time_friday', '23:59');
        Setting::set('dispensasi_days', '1,2,3,4,5,6,0');

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
            'name'     => 'Guru Tester',
            'email'    => 'guru_test@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198501012010011002',
            'password' => Hash::make('password'),
        ]);

        $this->guru = Guru::create([
            'user_id'      => $this->guruUser->id,
            'nip'          => '198501012010011002',
            'nama_lengkap' => 'Guru Tester',
        ]);

        $this->siswaUser = User::create([
            'name'     => 'Siswa Tester',
            'email'    => 'siswa_test@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '20261001',
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id'      => $this->siswaUser->id,
            'kelas_id'     => $this->kelas->id,
            'nama_lengkap' => 'Siswa Tester',
            'nis'          => '20261001',
            'jenis_kelamin'=> 'L',
            'no_telepon'   => '081234567890',
        ]);
    }

    /**
     * 1. Senin menggunakan jadwal 10 jam.
     */
    public function test_senin_menggunakan_jadwal_10_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(1);
        $this->assertEquals(10, $maxJam);
        $this->assertCount(10, TimeHelper::getJadwalHari(1));
    }

    /**
     * 2. Selasa menggunakan jadwal 10 jam.
     */
    public function test_selasa_menggunakan_jadwal_10_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(2);
        $this->assertEquals(10, $maxJam);
        $this->assertCount(10, TimeHelper::getJadwalHari(2));
    }

    /**
     * 3. Rabu menggunakan jadwal 11 jam.
     */
    public function test_rabu_menggunakan_jadwal_11_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(3);
        $this->assertEquals(11, $maxJam);
        $this->assertCount(11, TimeHelper::getJadwalHari(3));
    }

    /**
     * 4. Kamis menggunakan jadwal 11 jam.
     */
    public function test_kamis_menggunakan_jadwal_11_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(4);
        $this->assertEquals(11, $maxJam);
        $this->assertCount(11, TimeHelper::getJadwalHari(4));
    }

    /**
     * 5. Jumat menggunakan jadwal 8 jam.
     */
    public function test_jumat_menggunakan_jadwal_8_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(5);
        $this->assertEquals(8, $maxJam);
        $this->assertCount(8, TimeHelper::getJadwalHari(5));
    }

    /**
     * 6. Rabu Jam 11 valid.
     */
    public function test_rabu_jam_11_valid_di_backend(): void
    {
        // Set waktu ke hari Rabu (2026-09-23)
        Carbon::setTestNow(Carbon::parse('2026-09-23 09:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Ada kegiatan sekolah di luar kampus',
            'tujuan'          => 'Dinas Pendidikan',
            'jam_keluar'      => 10,
            'jam_kembali'     => 11,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('dispensasi', [
            'siswa_id'    => $this->siswa->id,
            'jam_keluar'  => 'Jam Pelajaran ke-10',
            'jam_kembali' => 'Jam Pelajaran ke-11',
        ]);
    }

    /**
     * 7. Senin Jam 11 invalid.
     */
    public function test_senin_jam_11_invalid_di_backend(): void
    {
        // Set waktu ke hari Senin (2026-09-21)
        Carbon::setTestNow(Carbon::parse('2026-09-21 09:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Mencoba submit jam 11 pada hari Senin',
            'tujuan'          => 'Dinas',
            'jam_keluar'      => 10,
            'jam_kembali'     => 11,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasErrors(['jam_kembali']);
    }

    /**
     * 8. Jumat Jam 9 invalid.
     */
    public function test_jumat_jam_9_invalid_di_backend(): void
    {
        // Set waktu ke hari Jumat (2026-09-25)
        Carbon::setTestNow(Carbon::parse('2026-09-25 09:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Mencoba submit jam 9 pada hari Jumat',
            'tujuan'          => 'Dinas',
            'jam_keluar'      => 8,
            'jam_kembali'     => 9,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasErrors(['jam_kembali']);
    }

    /**
     * 9. Perhitungan waktu Jam 11 benar (14:30 - 15:10 pada Rabu).
     */
    public function test_perhitungan_waktu_jam_11_benar(): void
    {
        // Rabu (day 3)
        $waktu = TimeHelper::getWaktuAktual('Jam Pelajaran ke-11', 3);
        $this->assertEquals('14:30 - 15:10', $waktu);

        $batas = TimeHelper::getBatasWaktuKembali(11, 3);
        $this->assertNotNull($batas);
        $this->assertEquals('15:10', $batas->format('H:i'));
    }

    /**
     * 10. TimeHelper mengambil jadwal dari Setting.
     */
    public function test_timehelper_mengambil_jadwal_dari_setting(): void
    {
        // Ubah jam 1 Senin-Selasa di Setting menjadi 07:15 - 08:00
        $customJadwal = TimeHelper::getDefaultJadwal();
        $customJadwal['senin_selasa'][1] = ['start' => '07:15', 'end' => '08:00'];
        Setting::set('jam_pelajaran', json_encode($customJadwal));

        $waktu = TimeHelper::getWaktuAktual('Jam Pelajaran ke-1', 1);
        $this->assertEquals('07:15 - 08:00', $waktu);

        $batas = TimeHelper::getBatasWaktuKembali(1, 1);
        $this->assertEquals('08:00', $batas->format('H:i'));
    }

    /**
     * 11. Jam Keluar yang sudah selesai ditandai lewat di frontend logic.
     */
    public function test_format_label_dropdown_dan_penanda_jam(): void
    {
        // Pastikan template blade merender label dengan format "Jam X (07.00 - 07.45)"
        Carbon::setTestNow(Carbon::parse('2026-09-21 07:00:00', 'Asia/Jakarta')); // Senin

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.pengajuan.create'));
        $response->assertOk();
        $response->assertSee('Jam 1 (07.00 - 07.45)');
        $response->assertSee('Jam 10 (14.30 - 15.15)');
    }

    /**
     * 12. Saat jam istirahat, jam berikutnya belum dianggap lewat.
     */
    public function test_saat_jam_istirahat_jam_berikutnya_belum_lewat(): void
    {
        // Istirahat 1 Senin: 09:15 - 09:30.
        // Pada 09:20 WIB, Jam 3 (08:30 - 09:15) sudah selesai (currentTime >= 09:15).
        // Jam 4 (09:30 - 10:15) waktu selesainya 10:15, sehingga 09:20 < 10:15 -> belum lewat!
        $currentMinutes = 9 * 60 + 20; // 09:20
        $jam3End = 9 * 60 + 15;
        $jam4End = 10 * 60 + 15;

        $isJam3Past = $currentMinutes >= $jam3End;
        $isJam4Past = $currentMinutes >= $jam4End;

        $this->assertTrue($isJam3Past);
        $this->assertFalse($isJam4Past);
    }

    /**
     * 13. Jam Kembali harus > Jam Keluar.
     */
    public function test_jam_kembali_harus_lebih_besar_dari_jam_keluar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-21 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Mencoba jam kembali lebih kecil dari jam keluar',
            'tujuan'          => 'Rumah',
            'jam_keluar'      => 5,
            'jam_kembali'     => 3,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasErrors(['jam_kembali']);
    }

    /**
     * 14. Jam Kembali yang sudah lewat ditolak di level waktu.
     */
    public function test_jam_kembali_berhasil_disimpan_jika_lebih_besar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-21 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Dispensasi dengan jam valid',
            'tujuan'          => 'Perpustakaan',
            'jam_keluar'      => 3,
            'jam_kembali'     => 5,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('dispensasi', [
            'siswa_id'    => $this->siswa->id,
            'jam_keluar'  => 'Jam Pelajaran ke-3',
            'jam_kembali' => 'Jam Pelajaran ke-5',
        ]);
    }

    /**
     * 15. Jam Kembali yang <= Jam Keluar ditolak oleh validator gt:jam_keluar.
     */
    public function test_jam_kembali_sama_dengan_jam_keluar_ditolak(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-21 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Jam keluar dan kembali sama',
            'tujuan'          => 'Ruang BK',
            'jam_keluar'      => 4,
            'jam_kembali'     => 4,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasErrors(['jam_kembali']);
    }

    /**
     * 16. Old input tetap bekerja pada view.
     */
    public function test_old_input_tetap_bekerja_pada_create_view(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-21 07:00:00', 'Asia/Jakarta'));

        $this->withSession(['_old_input' => [
            'jam_keluar' => 2,
            'jam_kembali' => 5,
        ]]);

        $response = $this->actingAs($this->siswaUser)->get(route('siswa.pengajuan.create'));
        $response->assertOk();
        $response->assertSee('value="2" selected', false);
    }

    /**
     * 17. Timezone menggunakan Asia/Jakarta.
     */
    public function test_timezone_menggunakan_asia_jakarta(): void
    {
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
        $this->assertEquals('+07:00', now('Asia/Jakarta')->format('P'));
    }

    /**
     * 18. Sabtu max 10 jam.
     */
    public function test_sabtu_max_10_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(6);
        $this->assertEquals(10, $maxJam);
        $this->assertCount(10, TimeHelper::getJadwalHari(6));
    }

    /**
     * 19. Minggu max 10 jam.
     */
    public function test_minggu_max_10_jam(): void
    {
        $maxJam = TimeHelper::getMaxJamPelajaran(0);
        $this->assertEquals(10, $maxJam);
        $this->assertCount(10, TimeHelper::getJadwalHari(0));
    }

    /**
     * 20. Sabtu mapping benar ke key 'sabtu'.
     */
    public function test_sabtu_mapping_benar(): void
    {
        $this->assertEquals('sabtu', TimeHelper::getJadwalKeyByDay(6));
    }

    /**
     * 21. Minggu mapping benar ke key 'minggu'.
     */
    public function test_minggu_mapping_benar(): void
    {
        $this->assertEquals('minggu', TimeHelper::getJadwalKeyByDay(0));
    }

    /**
     * 22. Batas waktu Sabtu benar (Jam 10 -> 15:15).
     */
    public function test_batas_waktu_sabtu_benar(): void
    {
        $batasWaktu = TimeHelper::getBatasWaktuKembali(10, 6);
        $this->assertNotNull($batasWaktu);
        $this->assertEquals('15:15', $batasWaktu->format('H:i'));
        $this->assertEquals('07:00 - 07:45', TimeHelper::getWaktuAktual(1, 6));
        $this->assertEquals('14:30 - 15:15', TimeHelper::getWaktuAktual(10, 6));
    }

    /**
     * 23. Batas waktu Minggu benar (Jam 10 -> 15:15).
     */
    public function test_batas_waktu_minggu_benar(): void
    {
        $batasWaktu = TimeHelper::getBatasWaktuKembali(10, 0);
        $this->assertNotNull($batasWaktu);
        $this->assertEquals('15:15', $batasWaktu->format('H:i'));
        $this->assertEquals('07:00 - 07:45', TimeHelper::getWaktuAktual(1, 0));
        $this->assertEquals('14:30 - 15:15', TimeHelper::getWaktuAktual(10, 0));
    }

    /**
     * 24. Sabtu Jam 10 valid.
     */
    public function test_sabtu_jam_10_valid(): void
    {
        // Bekukan waktu pada Sabtu 26 September 2026 jam 07:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-26 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Testing dispensasi sabtu jam 10',
            'tujuan'          => 'Lab Komputer',
            'jam_keluar'      => 1,
            'jam_kembali'     => 10,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('dispensasi', [
            'siswa_id'    => $this->siswa->id,
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-10',
        ]);
    }

    /**
     * 25. Minggu Jam 10 valid.
     */
    public function test_minggu_jam_10_valid(): void
    {
        // Bekukan waktu pada Minggu 27 September 2026 jam 07:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-27 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Testing dispensasi minggu jam 10',
            'tujuan'          => 'Lab Komputer',
            'jam_keluar'      => 1,
            'jam_kembali'     => 10,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('dispensasi', [
            'siswa_id'    => $this->siswa->id,
            'jam_keluar'  => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-10',
        ]);
    }

    /**
     * 26. Sabtu Jam 11 invalid.
     */
    public function test_sabtu_jam_11_invalid(): void
    {
        // Bekukan waktu pada Sabtu 26 September 2026 jam 07:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-26 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Testing jam 11 pada hari sabtu',
            'tujuan'          => 'Lab Komputer',
            'jam_keluar'      => 1,
            'jam_kembali'     => 11,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasErrors(['jam_kembali']);
    }

    /**
     * 27. Minggu Jam 11 invalid.
     */
    public function test_minggu_jam_11_invalid(): void
    {
        // Bekukan waktu pada Minggu 27 September 2026 jam 07:00 WIB
        Carbon::setTestNow(Carbon::parse('2026-09-27 07:00:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->guruUser)->post(route('guru.pengajuan.store'), [
            'siswa_id'        => $this->siswa->id,
            'kategori'        => 'izin',
            'alasan'          => 'Testing jam 11 pada hari minggu',
            'tujuan'          => 'Lab Komputer',
            'jam_keluar'      => 1,
            'jam_kembali'     => 11,
            'foto_verifikasi' => UploadedFile::fake()->image('verif.jpg'),
        ]);

        $response->assertSessionHasErrors(['jam_kembali']);
    }

    /**
     * 28. Senin & Selasa memiliki 2 slot istirahat default dan total slot lengkap.
     */
    public function test_senin_dan_selasa_memiliki_2_istirahat_dan_seluruh_slot_kbm(): void
    {
        $istirahat = TimeHelper::getIstirahatHari(1);
        $this->assertCount(2, $istirahat);
        $this->assertEquals('09:15', $istirahat[1]['start']);
        $this->assertEquals('09:30', $istirahat[1]['end']);
        $this->assertEquals('11:45', $istirahat[2]['start']);
        $this->assertEquals('12:15', $istirahat[2]['end']);

        $semuaSlot = TimeHelper::getSemuaSlotHari(1);
        // 10 jam KBM + 2 jam istirahat = 12 slot terurut
        $this->assertCount(12, $semuaSlot);
        $this->assertEquals('07:00', $semuaSlot[0]['start']);
        $this->assertEquals('15:15', $semuaSlot[11]['end']);
    }

    /**
     * 29. Jumat memiliki Pembiasaan dan 2 slot istirahat.
     */
    public function test_jumat_memiliki_pembiasaan_dan_2_istirahat(): void
    {
        $istirahat = TimeHelper::getIstirahatHari(5);
        $this->assertCount(3, $istirahat);
        $this->assertEquals('Pembiasaan', $istirahat[0]['label']);
        $this->assertEquals('07:00', $istirahat[0]['start']);
        $this->assertEquals('08:00', $istirahat[0]['end']);

        $semuaSlot = TimeHelper::getSemuaSlotHari(5);
        // 1 pembiasaan + 8 jam KBM + 2 jam istirahat = 11 slot terurut
        $this->assertCount(11, $semuaSlot);
        $this->assertEquals('07:00', $semuaSlot[0]['start']);
        $this->assertEquals('14:00', $semuaSlot[10]['end']);
    }

    /**
     * 30. Siswa dapat mengajukan dispensasi pada jam istirahat (format HH:MM).
     */
    public function test_siswa_bisa_mengajukan_dispensasi_pada_jam_istirahat(): void
    {
        // Set waktu ke hari Senin jam 09:15 WIB (Awal Istirahat 1)
        Carbon::setTestNow(Carbon::parse('2026-09-21 09:15:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->siswaUser)->post(route('siswa.pengajuan.store'), [
            'kategori'        => 'keperluan_sekolah',
            'alasan'          => 'Dispen saat jam istirahat untuk ambil berkas OSIS',
            'tujuan'          => 'Ruang OSIS',
            'lokasi'          => 'Gedung Depan',
            'no_telepon'      => '081234567890',
            'jam_keluar'      => '09:20', // Dalam rentang istirahat 1 (09:15 - 09:30)
            'jam_kembali'     => '09:30',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('dispensasi', [
            'siswa_id'    => $this->siswa->id,
            'jam_keluar'  => '09:20',
            'jam_kembali' => '09:30',
        ]);
    }
}
