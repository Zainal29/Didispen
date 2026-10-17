<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Organisasi;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrganisasiKetuaDispensasiTest extends TestCase
{
    use RefreshDatabase;

    private User $superadminUser;
    private User $adminUser;
    private User $ketuaUser;
    private Siswa $ketuaSiswa;
    private User $biasaUser;
    private Siswa $biasaSiswa;
    private Organisasi $pramukaOrg;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('dispensasi_start_time', '00:00');
        Setting::set('dispensasi_end_time', '23:59');
        Setting::set('dispensasi_days', '1,2,3,4,5,6,7');

        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 2', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        // Super Admin
        $this->superadminUser = User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_superadmin' => true,
            'nis_nip' => 'SUP001',
        ]);

        // Admin Biasa
        $this->adminUser = User::create([
            'name' => 'Admin Biasa',
            'email' => 'adminbiasa@sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_superadmin' => false,
            'nis_nip' => 'ADM002',
        ]);

        // Siswa Calon Ketua (Pramuka)
        $this->ketuaUser = User::create([
            'name' => 'BELLA KETUA PRAMUKA',
            'email' => 'bella@sch.id',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'is_superadmin' => false,
            'nis_nip' => '5001',
        ]);
        $this->ketuaSiswa = Siswa::create([
            'user_id' => $this->ketuaUser->id,
            'nis_nip' => '5001',
            'nama_lengkap' => 'BELLA KETUA PRAMUKA',
            'kelas_id' => $kelas->id,
            'jurusan_id' => $jurusan->id,
            'status_aktif' => true,
            'no_telepon' => '081234567890',
        ]);

        // Siswa Biasa (Bukan Ketua)
        $this->biasaUser = User::create([
            'name' => 'DONI SISWA BIASA',
            'email' => 'doni@sch.id',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'is_superadmin' => false,
            'nis_nip' => '5002',
        ]);
        $this->biasaSiswa = Siswa::create([
            'user_id' => $this->biasaUser->id,
            'nis_nip' => '5002',
            'nama_lengkap' => 'DONI SISWA BIASA',
            'kelas_id' => $kelas->id,
            'jurusan_id' => $jurusan->id,
            'status_aktif' => true,
            'no_telepon' => '081234567891',
        ]);

        // Organisasi Pramuka
        $this->pramukaOrg = Organisasi::create([
            'nama_organisasi' => 'Pramuka Gudep',
            'singkatan' => 'PRAMUKA',
            'kategori' => 'ekstrakurikuler',
            'ketua_siswa_id' => $this->ketuaSiswa->id,
            'status_aktif' => true,
        ]);
    }

    public function test_superadmin_can_manage_organisasi(): void
    {
        $response = $this->actingAs($this->superadminUser)->get(route('admin.organisasi.index'));
        $response->assertOk();
        $response->assertSee('Super Admin Mode');
        $response->assertSee('Pramuka Gudep');
        $response->assertSee('BELLA KETUA PRAMUKA');

        // Super Admin update ketua
        $updateResp = $this->actingAs($this->superadminUser)->put(route('admin.organisasi.update', $this->pramukaOrg->id), [
            'nama_organisasi' => 'Pramuka Inti',
            'singkatan' => 'PRAMUKA',
            'kategori' => 'ekstrakurikuler',
            'ketua_siswa_id' => $this->biasaSiswa->id,
            'status_aktif' => 1,
        ]);
        $updateResp->assertRedirect(route('admin.organisasi.index'));
        $this->assertEquals($this->biasaSiswa->id, $this->pramukaOrg->fresh()->ketua_siswa_id);
    }

    public function test_admin_biasa_cannot_update_or_delete_organisasi(): void
    {
        // Admin biasa can view list
        $viewResp = $this->actingAs($this->adminUser)->get(route('admin.organisasi.index'));
        $viewResp->assertOk();
        $viewResp->assertSee('Mode Pantau');

        // Admin biasa cannot update
        $updateResp = $this->actingAs($this->adminUser)->put(route('admin.organisasi.update', $this->pramukaOrg->id), [
            'nama_organisasi' => 'Pramuka Diubah Admin Biasa',
            'kategori' => 'ekstrakurikuler',
            'status_aktif' => 1,
        ]);
        $updateResp->assertForbidden();

        // Admin biasa cannot delete
        $deleteResp = $this->actingAs($this->adminUser)->delete(route('admin.organisasi.destroy', $this->pramukaOrg->id));
        $deleteResp->assertForbidden();
    }

    public function test_admin_siswa_table_shows_ketua_badge(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.siswa.index'));
        $response->assertOk();
        $response->assertSee('Ketua PRAMUKA');
    }

    public function test_form_create_pengajuan_shows_diabaikan_badge_for_siswa_biasa(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 08:30:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->biasaUser)->get(route('siswa.pengajuan.create'));
        $response->assertOk();
        $response->assertSee('Diabaikan');
    }

    public function test_form_create_pengajuan_shows_diabaikan_badge_for_siswa_ketua(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 08:30:00', 'Asia/Jakarta'));

        $respKetua = $this->actingAs($this->ketuaUser)->get(route('siswa.pengajuan.create'));
        $respKetua->assertOk();
        $respKetua->assertSee('Diabaikan');
    }

    public function test_form_create_pengajuan_shows_superadmin_badge_for_superadmin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-14 08:30:00', 'Asia/Jakarta'));

        $this->ketuaUser->update(['is_superadmin' => true]);
        $respSuper = $this->actingAs($this->ketuaUser)->get(route('siswa.pengajuan.create'));
        $respSuper->assertOk();
        $respSuper->assertSee('Super Admin');
    }

    public function test_siswa_biasa_cannot_submit_dispensasi_organisasi(): void
    {
        Carbon::setTestNow('2026-10-14 08:30:00');

        $response = $this->actingAs($this->biasaUser)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'Pramuka Gudep',
            'tanggal_izin' => '2026-10-14',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mengikuti lomba pramuka se-kabupaten',
            'tujuan' => 'Alun-alun Jepara',
            'lokasi' => 'Jepara',
            'no_telepon' => '081234567891',
            'jam_keluar' => '09:00',
            'jam_kembali' => '12:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $response->assertSessionHasErrors('tipe_pengajuan');
        $this->assertStringContainsString('masih dalam pengerjaan cuy', session('errors')->first('tipe_pengajuan'));
    }

    public function test_siswa_ketua_cannot_submit_dispensasi_organisasi(): void
    {
        Carbon::setTestNow('2026-10-14 08:30:00');

        $respKetua = $this->actingAs($this->ketuaUser)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'Pramuka Gudep',
            'tanggal_izin' => '2026-10-14',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mengikuti lomba pramuka se-kabupaten',
            'tujuan' => 'Alun-alun Jepara',
            'lokasi' => 'Jepara',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'jam_kembali' => '12:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $respKetua->assertSessionHasErrors('tipe_pengajuan');
        $this->assertStringContainsString('masih dalam pengerjaan cuy', session('errors')->first('tipe_pengajuan'));
    }

    public function test_superadmin_can_submit_dispensasi_organisasi_for_testing(): void
    {
        Carbon::setTestNow('2026-10-14 08:30:00');

        $this->ketuaUser->update(['is_superadmin' => true]);

        $response = $this->actingAs($this->ketuaUser)->post(route('siswa.pengajuan.store'), [
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'Pramuka Gudep',
            'tanggal_izin' => '2026-10-14',
            'kategori' => 'keperluan_sekolah',
            'alasan' => 'Mengikuti kemah bhakti pramuka wira',
            'tujuan' => 'Bumi Perkemahan Pakis Adhi',
            'lokasi' => 'Pakis Adhi',
            'no_telepon' => '081234567890',
            'jam_keluar' => '09:00',
            'jam_kembali' => '13:00',
            'foto_verifikasi' => UploadedFile::fake()->image('selfie_ketua.jpg'),
            'anggota_ids' => [$this->biasaSiswa->id],
        ]);

        $response->assertRedirect(route('siswa.pengajuan.index'));

        $this->assertDatabaseHas('dispensasi', [
            'siswa_id' => $this->ketuaSiswa->id,
            'tipe_pengajuan' => 'organisasi',
            'nama_organisasi' => 'Pramuka Gudep',
            'organisasi_id' => $this->pramukaOrg->id,
        ]);
    }
}
