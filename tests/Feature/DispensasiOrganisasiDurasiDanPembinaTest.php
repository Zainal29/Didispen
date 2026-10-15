<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Organisasi;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DispensasiOrganisasiDurasiDanPembinaTest extends TestCase
{
    use RefreshDatabase;

    protected User $userKetua;
    protected Siswa $ketuaSiswa;
    protected User $userAnggota;
    protected Siswa $anggotaSiswa;
    protected Guru $pembinaResmi;
    protected Organisasi $organisasi;
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

        $jurusan = Jurusan::create([
            'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',
            'kode_jurusan' => 'AKL',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII AKL 1',
            'tingkat' => 'XII',
            'jurusan_id' => $jurusan->id,
        ]);

        // Ketua
        $this->userKetua = User::create([
            'name' => 'Muhammad Denis Afriza',
            'email' => 'denis@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis_nip' => '4853',
        ]);

        $this->ketuaSiswa = Siswa::create([
            'user_id' => $this->userKetua->id,
            'kelas_id' => $this->kelas->id,
            'nama_lengkap' => 'MUHAMMAD DENIS AFRIZA',
            'nis_nip' => '4853',
            'jenis_kelamin' => 'L',
            'no_telepon' => '081234567890',
            'status_aktif' => true,
        ]);

        // Anggota
        $this->userAnggota = User::create([
            'name' => 'Ahmad Subandi',
            'email' => 'subandi@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis_nip' => '4854',
        ]);

        $this->anggotaSiswa = Siswa::create([
            'user_id' => $this->userAnggota->id,
            'kelas_id' => $this->kelas->id,
            'nama_lengkap' => 'AHMAD SUBANDI',
            'nis_nip' => '4854',
            'jenis_kelamin' => 'L',
            'no_telepon' => '081234567891',
            'status_aktif' => true,
        ]);

        // Guru Pembina
        $userGuru = User::create([
            'name' => 'Shaifur Rizqi Zein',
            'email' => 'shaifur@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nis_nip' => '198501012010011001',
        ]);

        $this->pembinaResmi = Guru::create([
            'user_id' => $userGuru->id,
            'nama_lengkap' => 'Shaifur Rizqi Zein, S.Pd',
            'nip' => '198501012010011001',
            'mata_pelajaran' => 'Pramuka & Olahraga',
            'status_aktif' => true,
        ]);

        // Organisasi Pramuka
        $this->organisasi = Organisasi::create([
            'nama_organisasi' => 'PRAMUKA KH. ACHMAD FAUZAN (03.161)',
            'singkatan' => 'Pramuka',
            'kategori' => 'ekstrakurikuler',
            'ketua_siswa_id' => $this->ketuaSiswa->id,
            'pembina_guru_id' => $this->pembinaResmi->id,
            'status_aktif' => true,
        ]);
    }

    /**
     * Test 1: Pembina resmi organisasi otomatis muncul di oldGurusList saat Ketua membuka form create
     */
    public function test_ketua_organisasi_melihat_pembina_default_otomatis_di_create(): void
    {
        $response = $this->actingAs($this->userKetua)->get(route('siswa.pengajuan.create'));

        $response->assertOk();
        $response->assertViewHas('oldGurusList', function ($list) {
            return collect($list)->contains('id', $this->pembinaResmi->id);
        });
        $response->assertSee('Shaifur Rizqi Zein, S.Pd');
    }

    /**
     * Test 2: Pengajuan organisasi dengan tanggal selesai dan durasi hari multi-hari
     */
    public function test_pengajuan_organisasi_dengan_tanggal_selesai_dan_durasi_hari(): void
    {
        $response = $this->actingAs($this->userKetua)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'PRAMUKA KH. ACHMAD FAUZAN (03.161)',
            'tanggal_izin' => '2026-10-12',
            'tanggal_selesai' => '2026-10-14', // 3 hari: 12, 13, 14
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mengikuti Jambore Daerah Tingkat Provinsi Jawa Tengah.',
            'tujuan' => 'Bumi Perkemahan Karanggeneng Semarang',
            'lokasi' => 'Semarang',
            'no_telepon' => '081234567890',
            'jam_keluar' => '08:00',
            'jam_kembali' => '14:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
            'anggota_ids' => [$this->anggotaSiswa->id],
            'guru_ids' => [$this->pembinaResmi->id],
        ]);

        $response->assertRedirect(route('siswa.pengajuan.index'));

        $dispensasi = Dispensasi::where('siswa_id', $this->ketuaSiswa->id)->first();
        $this->assertNotNull($dispensasi);
        $this->assertEquals('organisasi', $dispensasi->tipe_pengajuan);
        $this->assertEquals('PRAMUKA KH. ACHMAD FAUZAN (03.161)', $dispensasi->nama_organisasi);
        $this->assertEquals('2026-10-12', $dispensasi->tanggal_izin->format('Y-m-d'));
        $this->assertEquals('2026-10-14', $dispensasi->tanggal_selesai->format('Y-m-d'));
        $this->assertEquals(3, $dispensasi->durasi_hari);
        $this->assertTrue($dispensasi->gurus->contains($this->pembinaResmi->id));
        $this->assertTrue($dispensasi->anggotaSiswa->contains($this->anggotaSiswa->id));
    }

    /**
     * Test 3: Validasi tanggal selesai tidak boleh lebih awal dari tanggal mulai
     */
    public function test_validasi_tanggal_selesai_tidak_boleh_lebih_awal(): void
    {
        $response = $this->actingAs($this->userKetua)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'PRAMUKA KH. ACHMAD FAUZAN (03.161)',
            'tanggal_izin' => '2026-10-14',
            'tanggal_selesai' => '2026-10-12', // Error: lebih awal
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mengikuti kegiatan perlombaan kepramukaan.',
            'tujuan' => 'Semarang',
            'no_telepon' => '081234567890',
            'jam_keluar' => '08:00',
            'jam_kembali' => '14:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertSessionHasErrors('tanggal_selesai');
    }

    /**
     * Test 4: API cari siswa mendukung filter kelas_id untuk checklist rombongan
     */
    public function test_api_cari_siswa_dengan_filter_kelas_id(): void
    {
        $response = $this->actingAs($this->userKetua)->getJson(
            route('siswa.api.cari-siswa', ['kelas_id' => $this->kelas->id])
        );

        $response->assertOk();
        $data = $response->json();

        // Mengembalikan anggota satu kelas kecuali diri sendiri (Ketua)
        $this->assertCount(1, $data);
        $this->assertEquals('AHMAD SUBANDI', $data[0]['nama']);
        $this->assertEquals('XII AKL 1', $data[0]['kelas']);
    }

    /**
     * Test 5: PDF surat dispensasi organisasi mencantumkan catatan pelaksanaan durasi hari
     */
    public function test_pdf_surat_dispensasi_organisasi_mencantumkan_catatan_durasi_hari(): void
    {
        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->ketuaSiswa->id,
            'guru_id' => null,
            'nomor_surat' => Dispensasi::generateNomorSurat(),
            'tipe_pengajuan' => 'organisasi',
            'organisasi_id' => $this->organisasi->id,
            'nama_organisasi' => 'PRAMUKA KH. ACHMAD FAUZAN (03.161)',
            'tanggal_izin' => '2026-10-14',
            'tanggal_selesai' => '2026-10-16',
            'durasi_hari' => 3,
            'sampai_pulang' => true,
            'status' => 'disetujui',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mengikuti Jambore Daerah Pramuka.',
            'tujuan' => 'Semarang',
            'lokasi' => 'Semarang',
            'jam_keluar' => '08:00',
            'jam_kembali' => 'pulang',
            'dibuat_manual_oleh_guru' => false,
        ]);

        $dispensasi->gurus()->sync([$this->pembinaResmi->id]);
        $dispensasi->anggotaSiswa()->sync([$this->anggotaSiswa->id]);

        $html = view('pdf.surat-dispensasi', compact('dispensasi'))->render();

        $this->assertStringContainsString('Catatan Pelaksanaan:', $html);
        $this->assertStringContainsString('(3 hari)', $html);
        $this->assertStringContainsString('Shaifur Rizqi Zein, S.Pd', $html);
    }
}
