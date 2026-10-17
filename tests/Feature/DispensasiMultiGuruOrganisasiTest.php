<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
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

class DispensasiMultiGuruOrganisasiTest extends TestCase
{
    use RefreshDatabase;

    protected User $userSiswa;
    protected Siswa $siswa;
    protected User $userTeman;
    protected Siswa $teman;
    protected Guru $guru1;
    protected Guru $guru2;

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
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XII PPLG 2',
            'tingkat' => 'XII',
            'jurusan_id' => $jurusan->id,
        ]);

        $this->userSiswa = User::create([
            'name' => 'Muhammad Zainal Arief',
            'email' => 'zainal@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'is_superadmin' => true,
            'nis_nip' => '4717',
        ]);

        $this->siswa = Siswa::create([
            'user_id' => $this->userSiswa->id,
            'kelas_id' => $kelas->id,
            'nis_nip' => '4717',
            'nama_lengkap' => 'Muhammad Zainal Arief',
            'jenis_kelamin' => 'L',
            'status_aktif' => true,
            'no_telepon' => '081234567890',
        ]);

        \App\Models\Organisasi::create([
            'nama_organisasi' => 'OSIS SMKN 1 Bangsri',
            'singkatan' => 'OSIS',
            'ketua_siswa_id' => $this->siswa->id,
            'status_aktif' => true,
        ]);

        $userTeman = User::create([
            'name' => 'Teman Anggota',
            'email' => 'teman@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'nis_nip' => '4718',
        ]);

        $this->teman = Siswa::create([
            'user_id' => $userTeman->id,
            'kelas_id' => $kelas->id,
            'nis_nip' => '4718',
            'nama_lengkap' => 'Teman Anggota',
            'jenis_kelamin' => 'L',
            'status_aktif' => true,
            'no_telepon' => '081234567891',
        ]);

        $userGuru1 = User::create([
            'name' => 'Bapak Guru Pembina 1',
            'email' => 'guru1@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nis_nip' => '197001010001',
        ]);

        $this->guru1 = Guru::create([
            'user_id' => $userGuru1->id,
            'nip' => '197001010001',
            'nama_lengkap' => 'Bapak Guru Pembina 1',
            'mata_pelajaran' => 'Pembina OSIS',
            'status_aktif' => true,
        ]);

        $userGuru2 = User::create([
            'name' => 'Ibu Guru Pembina 2',
            'email' => 'guru2@didispen.sch.id',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'nis_nip' => '197001010002',
        ]);

        $this->guru2 = Guru::create([
            'user_id' => $userGuru2->id,
            'nip' => '197001010002',
            'nama_lengkap' => 'Ibu Guru Pembina 2',
            'mata_pelajaran' => 'Pembina Pramuka',
            'status_aktif' => true,
        ]);
    }

    public function test_can_search_from_teachers_api()
    {
        $this->actingAs($this->userSiswa);

        $response = $this->getJson(route('siswa.api.cari-guru', ['q' => '']));
        $response->assertOk();
        $this->assertCount(2, $response->json());

        // Cari dengan kata kunci spesifik
        $responseFiltered = $this->getJson(route('siswa.api.cari-guru', ['q' => 'Pramuka']));
        $responseFiltered->assertOk();
        $this->assertCount(1, $responseFiltered->json());
        $this->assertEquals($this->guru2->id, $responseFiltered->json()[0]['id']);
    }

    public function test_can_create_organisasi_dispensasi_with_multiple_teachers()
    {
        $this->actingAs($this->userSiswa);

        $fakePhoto = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        $payload = [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'Pramuka & Tim Paskibra SMKN 1 Bangsri',
            'tanggal_izin' => '2026-10-12',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Menghadiri gladi resik upacara kemerdekaan tingkat kabupaten.',
            'tujuan' => 'Alun-alun Kabupaten',
            'lokasi' => 'Jl. Pemuda No. 1',
            'no_telepon' => '081234567890',
            'jam_keluar' => '08:00',
            'jam_kembali' => '13:00',
            'foto_verifikasi' => $fakePhoto,
            'anggota_ids' => [$this->teman->id],
            'guru_ids' => [$this->guru1->id, $this->guru2->id],
        ];

        $response = $this->post(route('siswa.pengajuan.store'), $payload);
        $response->assertRedirect(route('siswa.pengajuan.index'));

        $dispensasi = Dispensasi::where('nama_organisasi', 'Pramuka & Tim Paskibra SMKN 1 Bangsri')->first();
        $this->assertNotNull($dispensasi);
        $this->assertEquals('organisasi', $dispensasi->tipe_pengajuan);
        $this->assertCount(1, $dispensasi->anggotaSiswa);
        $this->assertCount(2, $dispensasi->gurus);
        $this->assertTrue($dispensasi->gurus->contains($this->guru1->id));
        $this->assertTrue($dispensasi->gurus->contains($this->guru2->id));

        // Test show detail menampilkan pembina
        $showResponse = $this->get(route('siswa.pengajuan.show', $dispensasi->id));
        $showResponse->assertOk();
        $showResponse->assertSee($this->guru1->nama_lengkap);
        $showResponse->assertSee($this->guru2->nama_lengkap);
    }

    public function test_jam_kembali_cannot_be_less_than_or_equal_to_jam_keluar()
    {
        $this->actingAs($this->userSiswa);

        $fakePhoto = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        $payload = [
            'tipe_pengajuan' => 'individu',
            'kategori' => 'sakit',
            'alasan' => 'Pemeriksaan kesehatan darurat di puskesmas terdekat.',
            'tujuan' => 'Puskesmas',
            'no_telepon' => '081234567890',
            'jam_keluar' => '10:00',
            'jam_kembali' => '09:00', // Salah: jam kembali lebih kecil dari jam keluar
            'foto_verifikasi' => $fakePhoto,
        ];

        $response = $this->post(route('siswa.pengajuan.store'), $payload);
        $response->assertSessionHasErrors(['jam_kembali']);
    }

    public function test_sampai_pulang_does_not_require_jam_kembali()
    {
        $this->actingAs($this->userSiswa);

        $fakePhoto = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        $payload = [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'PMR Wira Unit SMKN 1 Bangsri',
            'sampai_pulang' => '1',
            'tanggal_izin' => '2026-10-12',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Pelatihan kepalangmerahan tingkat cabang hingga sore.',
            'tujuan' => 'Markas PMI Cabang',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'foto_verifikasi' => $fakePhoto,
            'guru_ids' => [$this->guru1->id],
        ];

        $response = $this->post(route('siswa.pengajuan.store'), $payload);
        $response->assertRedirect(route('siswa.pengajuan.index'));

        $dispensasi = Dispensasi::where('nama_organisasi', 'PMR Wira Unit SMKN 1 Bangsri')->first();
        $this->assertNotNull($dispensasi);
        $this->assertTrue($dispensasi->sampai_pulang);
        $this->assertEquals('Sampai Pulang Sekolah', $dispensasi->jam_kembali);
        $this->assertCount(1, $dispensasi->gurus);
    }

    public function test_jam_keluar_dan_kembali_di_luar_jam_sekolah_ditolak_validasi()
    {
        $this->actingAs($this->userSiswa);

        $fakePhoto = UploadedFile::fake()->image('selfie.jpg', 640, 480);

        // Uji jam malam di luar jam sekolah (contoh: 21:11 / 09:11 PM dan 21:20 / 09:20 PM)
        $payload = [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'OSIS SMKN 1 Bangsri',
            'tanggal_izin' => '2026-10-12',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Rapat koordinasi perwakilan organisasi.',
            'tujuan' => 'Ruang OSIS',
            'no_telepon' => '081234567890',
            'jam_keluar' => '21:11',
            'jam_kembali' => '21:20',
            'foto_verifikasi' => $fakePhoto,
        ];

        $response = $this->post(route('siswa.pengajuan.store'), $payload);
        $response->assertSessionHasErrors(['jam_keluar']);
    }

    public function test_old_anggota_dan_guru_dihidrasi_saat_terjadi_kesalahan_validasi()
    {
        $this->actingAs($this->userSiswa);

        // Simulasi submit gagal karena foto verifikasi selfie belum disertakan
        $payload = [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'PMR Wira Unit SMKN 1 Bangsri',
            'tanggal_izin' => '2026-10-12',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Pelatihan kepalangmerahan tingkat cabang.',
            'tujuan' => 'Markas PMI Cabang',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'jam_kembali' => '11:00',
            // Tanpa foto_verifikasi
            'anggota_ids' => [$this->teman->id],
            'guru_ids' => [$this->guru1->id, $this->guru2->id],
        ];

        $response = $this->post(route('siswa.pengajuan.store'), $payload);
        $response->assertSessionHasErrors(['foto_verifikasi']);

        // Akses halaman create dengan session old input dari request sebelumnya
        $createResponse = $this->withSession([
            '_old_input' => $payload,
        ])->get(route('siswa.pengajuan.create'));

        $createResponse->assertOk();
        $createResponse->assertViewHas('oldAnggotaList');
        $createResponse->assertViewHas('oldGurusList');

        $oldAnggotaList = $createResponse->viewData('oldAnggotaList');
        $oldGurusList = $createResponse->viewData('oldGurusList');

        $this->assertCount(1, $oldAnggotaList);
        $this->assertEquals($this->teman->id, $oldAnggotaList[0]['id']);

        $this->assertCount(2, $oldGurusList);
        $this->assertEquals($this->guru1->id, $oldGurusList[0]['id']);
        $this->assertEquals($this->guru2->id, $oldGurusList[1]['id']);
    }
}
