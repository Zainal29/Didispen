<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDualRoleTest extends TestCase
{
    use RefreshDatabase;

    private User $superadminSiswa;
    private User $normalSiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::create(['nama_jurusan' => 'PPLG', 'kode_jurusan' => 'PPLG']);
        $kelas = Kelas::create(['nama_kelas' => 'XII PPLG 2', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);

        // Siswa dengan status Super Admin
        $this->superadminSiswa = User::create([
            'name' => 'MUHAMMAD ZAINAL ARIEF',
            'email' => '4717@smkn1bangsri.sch.id',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'is_superadmin' => true,
            'nis_nip' => '4717',
        ]);
        Siswa::create([
            'user_id' => $this->superadminSiswa->id,
            'nis_nip' => '4717',
            'nama_lengkap' => 'MUHAMMAD ZAINAL ARIEF',
            'kelas_id' => $kelas->id,
            'jurusan_id' => $jurusan->id,
            'status_aktif' => true,
        ]);

        // Siswa biasa
        $this->normalSiswa = User::create([
            'name' => 'Siswa Biasa',
            'email' => 'siswa@smkn1bangsri.sch.id',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'is_superadmin' => false,
            'nis_nip' => '9999',
        ]);
        Siswa::create([
            'user_id' => $this->normalSiswa->id,
            'nis_nip' => '9999',
            'nama_lengkap' => 'Siswa Biasa',
            'kelas_id' => $kelas->id,
            'jurusan_id' => $jurusan->id,
            'status_aktif' => true,
        ]);
    }

    public function test_superadmin_siswa_can_access_siswa_dashboard(): void
    {
        $response = $this->actingAs($this->superadminSiswa)->get(route('siswa.dashboard'));
        $response->assertOk();
        $response->assertSee('Panel Admin');
    }

    public function test_superadmin_siswa_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->superadminSiswa)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Portal Siswa');
    }

    public function test_normal_siswa_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->normalSiswa)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_artisan_command_make_superadmin(): void
    {
        $this->assertFalse($this->normalSiswa->isSuperAdmin());

        $this->artisan('make:superadmin', ['identifier' => '9999'])
            ->assertSuccessful();

        $this->assertTrue($this->normalSiswa->fresh()->isSuperAdmin());

        $this->artisan('make:superadmin', ['identifier' => '9999', '--remove' => true])
            ->assertSuccessful();

        $this->assertFalse($this->normalSiswa->fresh()->isSuperAdmin());
    }

    public function test_admin_can_view_superadmin_index_page(): void
    {
        $response = $this->actingAs($this->superadminSiswa)->get(route('admin.superadmin.index'));
        $response->assertOk();
        $response->assertSee('Manajemen Super Admin');
        $response->assertSee('MUHAMMAD ZAINAL ARIEF');
    }

    public function test_admin_can_grant_superadmin_via_web(): void
    {
        $this->assertFalse($this->normalSiswa->isSuperAdmin());

        $response = $this->actingAs($this->superadminSiswa)->post(route('admin.superadmin.store'), [
            'identifier' => '9999',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue($this->normalSiswa->fresh()->isSuperAdmin());
    }

    public function test_admin_can_revoke_superadmin_via_web(): void
    {
        $this->assertTrue($this->superadminSiswa->isSuperAdmin());

        // Buat admin lain agar bukan satu-satunya superadmin
        $otherAdmin = User::create([
            'name' => 'Staf Admin Lain',
            'email' => 'other@smkn1bangsri.sch.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_superadmin' => true,
        ]);

        $response = $this->actingAs($otherAdmin)->delete(route('admin.superadmin.destroy', $this->superadminSiswa));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertFalse($this->superadminSiswa->fresh()->isSuperAdmin());
    }

    public function test_admin_can_toggle_siswa_superadmin(): void
    {
        $siswa = Siswa::where('user_id', $this->normalSiswa->id)->firstOrFail();
        $this->assertFalse($this->normalSiswa->isSuperAdmin());

        // Toggle On
        $response = $this->actingAs($this->superadminSiswa)->patch(route('admin.siswa.toggle-superadmin', $siswa));
        $response->assertRedirect();
        $this->assertTrue($this->normalSiswa->fresh()->isSuperAdmin());

        // Toggle Off
        $response = $this->actingAs($this->superadminSiswa)->patch(route('admin.siswa.toggle-superadmin', $siswa));
        $response->assertRedirect();
        $this->assertFalse($this->normalSiswa->fresh()->isSuperAdmin());
    }
}
