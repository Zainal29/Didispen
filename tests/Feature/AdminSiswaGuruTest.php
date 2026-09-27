<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSiswaGuruTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Jurusan $jurusan;
    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-27 10:00:00', 'Asia/Jakarta'));

        $this->admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin_test@sch.id',
            'role'     => 'admin',
            'nis_nip'  => 'ADMIN001',
            'password' => Hash::make('password'),
        ]);

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
            'kode_jurusan' => 'PPLG',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII PPLG 1',
            'tingkat'    => 'XII',
            'jurusan_id' => $this->jurusan->id,
        ]);
    }

    /**
     * 1. Siswa dengan kelas_id NULL tetap tampil di Admin Data Siswa.
     */
    public function test_siswa_dengan_kelas_null_tetap_tampil_di_admin_siswa(): void
    {
        $userSiswa = User::create([
            'name'     => 'Siswa Unassigned',
            'email'    => 'siswa_unassigned@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '990001',
            'password' => Hash::make('password'),
        ]);

        $siswa = Siswa::create([
            'user_id'      => $userSiswa->id,
            'kelas_id'     => null, // Kelas NULL
            'jurusan_id'   => null, // Jurusan NULL
            'nama_lengkap' => 'Siswa Unassigned',
            'nis_nip'      => '990001',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
        $response->assertOk();
        $response->assertSee('SISWA UNASSIGNED', false);
        $response->assertSee('Belum ada', false);
    }

    /**
     * 2. Siswa dengan created_at NULL tidak menyebabkan fatal error.
     */
    public function test_siswa_dengan_created_at_null_tidak_crash(): void
    {
        $userSiswa = User::create([
            'name'     => 'Siswa No Timestamp',
            'email'    => 'siswa_notime@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '990002',
            'password' => Hash::make('password'),
        ]);

        $siswa = Siswa::create([
            'user_id'      => $userSiswa->id,
            'kelas_id'     => $this->kelas->id,
            'nama_lengkap' => 'Siswa No Timestamp',
            'nis_nip'      => '990002',
            'status_aktif' => true,
        ]);

        // Simulasikan created_at null (misal dari sinkronisasi database eksternal)
        Siswa::where('id', $siswa->id)->update(['created_at' => null]);

        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
        $response->assertOk();
        $response->assertSee('SISWA NO TIMESTAMP', false);
    }

    /**
     * 3. Siswa edit via AJAX mengembalikan JSON yang benar dan aman dari null relationship.
     */
    public function test_siswa_edit_via_ajax_mengembalikan_json(): void
    {
        $userSiswa = User::create([
            'name'     => 'Siswa Ajax Test',
            'email'    => 'siswa_ajax@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '990003',
            'password' => Hash::make('password'),
        ]);

        $siswa = Siswa::create([
            'user_id'       => $userSiswa->id,
            'kelas_id'      => $this->kelas->id,
            'jurusan_id'    => $this->jurusan->id,
            'nama_lengkap'  => 'Siswa Ajax Test',
            'nis_nip'       => '990003',
            'status_aktif'  => true,
            'tanggal_lahir' => '2008-05-15',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('admin.siswa.edit', $siswa->id), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept'           => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJson([
            'id'           => $siswa->id,
            'nama_lengkap' => 'SISWA AJAX TEST',
            'user'         => [
                'nis_nip' => '990003',
                'email'   => 'siswa_ajax@sch.id',
            ],
        ]);
    }

    /**
     * 4. Siswa edit via GET biasa (tanpa AJAX) melakukan redirect aman ke index (bukan View not found).
     */
    public function test_siswa_edit_via_get_biasa_redirect_ke_index(): void
    {
        $userSiswa = User::create([
            'name'     => 'Siswa Direct Nav',
            'email'    => 'siswa_direct@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '990004',
            'password' => Hash::make('password'),
        ]);

        $siswa = Siswa::create([
            'user_id'      => $userSiswa->id,
            'kelas_id'     => $this->kelas->id,
            'nama_lengkap' => 'Siswa Direct Nav',
            'nis_nip'      => '990004',
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.siswa.edit', $siswa->id));
        $response->assertRedirect(route('admin.siswa.index'));
    }

    /**
     * 5. Guru dengan nama berkarakter kutip tunggal (misal M. Syafi'i) dan alamat berkutip tidak merusak tombol Lihat Data.
     */
    public function test_guru_dengan_tanda_kutip_merender_data_guru_valid(): void
    {
        $userGuru = User::create([
            'name'     => "M. Syafi'i, S.Pd.",
            'email'    => 'syafii@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198001012005011005',
            'password' => Hash::make('password'),
        ]);

        $guru = Guru::create([
            'user_id'        => $userGuru->id,
            'nip'            => '198001012005011005',
            'nama_lengkap'   => "M. Syafi'i, S.Pd.",
            'email'          => 'syafii.pribadi@gmail.com',
            'mata_pelajaran' => 'Pendidikan Agama Islam',
            'alamat'         => "Jl. Jum'at Kliwon No. 7, RT 02/03",
            'status_aktif'   => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.guru.index'));
        $response->assertOk();
        $response->assertSee("M. Syafi&#039;i, S.Pd.", false);
        $response->assertSee('data-guru=', false);
        $response->assertSee('openModalFromButton(this)', false);
    }

    /**
     * 6. Guru dengan email atau profil parsial null tetap tampil dengan aman.
     */
    public function test_guru_dengan_email_null_tidak_crash(): void
    {
        $userGuru = User::create([
            'name'     => 'Guru Tanpa Email',
            'email'    => 'guru_noemail@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '199001012020011099',
            'password' => Hash::make('password'),
        ]);

        $guru = Guru::create([
            'user_id'        => $userGuru->id,
            'nip'            => '199001012020011099',
            'nama_lengkap'   => 'Guru Tanpa Email',
            'email'          => null,
            'mata_pelajaran' => null,
            'no_telepon'     => null,
            'alamat'         => null,
            'status_aktif'   => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.guru.index'));
        $response->assertOk();
        $response->assertSee('Guru Tanpa Email', false);
        $response->assertSee('Belum tersedia', false);
    }

    /**
     * 7. Pencarian siswa dan guru dengan filter bekerja tanpa exception.
     */
    public function test_search_siswa_dan_guru_berjalan_tanpa_exception(): void
    {
        $responseSiswa = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['search' => 'PPLG']));
        $responseSiswa->assertOk();

        $responseGuru = $this->actingAs($this->admin)->get(route('admin.guru.index', ['search' => 'Matematika']));
        $responseGuru->assertOk();
    }

    /**
     * 8. Modal form pada halaman Admin Siswa memiliki struktur centering yang tepat dan endpoint AJAX edit berfungsi.
     */
    public function test_admin_siswa_modal_dan_ajax_edit_berfungsi(): void
    {
        $userSiswa = User::create([
            'name'     => 'Budi Siswa',
            'email'    => 'budi_siswa@sch.id',
            'role'     => 'siswa',
            'nis_nip'  => '123456',
            'password' => Hash::make('password'),
        ]);

        $siswa = Siswa::create([
            'user_id'      => $userSiswa->id,
            'kelas_id'     => $this->kelas->id,
            'jurusan_id'   => $this->jurusan->id,
            'nama_lengkap' => 'Budi Siswa',
            'nis_nip'      => '123456',
            'status_aktif' => true,
        ]);

        $responseView = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
        $responseView->assertOk();
        $responseView->assertSee('id="formModal"', false);
        $responseView->assertSee('id="siswaForm"', false);

        // Test AJAX JSON response for modal populate
        $responseAjax = $this->actingAs($this->admin)->getJson(route('admin.siswa.edit', $siswa->id));
        $responseAjax->assertOk();
        $responseAjax->assertJson([
            'id'           => $siswa->id,
            'nama_lengkap' => 'BUDI SISWA',
            'user'         => [
                'nis_nip' => '123456',
                'email'   => 'budi_siswa@sch.id',
            ]
        ]);
    }

    /**
     * 9. Modal form pada halaman Admin Guru memiliki struktur yang tepat dan tombol trigger.
     */
    public function test_admin_guru_modal_dan_trigger_berfungsi(): void
    {
        $responseView = $this->actingAs($this->admin)->get(route('admin.guru.index'));
        $responseView->assertOk();
        $responseView->assertSee('id="modal"', false);
        $responseView->assertSee('id="form"', false);
        $responseView->assertSee('id="modalTitle"', false);
    }
}

