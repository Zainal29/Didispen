<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityPromptModalTest extends TestCase
{
    use RefreshDatabase;

    private function createSiswaUser(array $userAttributes = [], array $siswaAttributes = []): array
    {
        $jurusan = Jurusan::create([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kode_jurusan' => 'RPL',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XII RPL 1',
            'tingkat'    => 'XII',
            'jurusan_id' => $jurusan->id,
        ]);

        $user = User::create(array_merge([
            'name'     => 'Siswa Default',
            'email'    => 'siswa@sekolah.sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'), // default password
            'nis_nip'  => '12345678',
        ], $userAttributes));

        $siswa = Siswa::create(array_merge([
            'user_id'      => $user->id,
            'nis_nip'      => '12345678',
            'nama_lengkap' => $user->name,
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
            'jenis_kelamin' => 'L',
        ], $siswaAttributes));

        return [$user, $siswa];
    }

    public function test_user_with_default_password_needs_security_update(): void
    {
        [$user] = $this->createSiswaUser();

        $this->assertTrue($user->isDefaultPassword());
        $this->assertTrue($user->needsSecurityUpdate());
    }

    public function test_user_can_update_security_profile_and_sync_phone(): void
    {
        [$user, $siswa] = $this->createSiswaUser();

        $response = $this->actingAs($user)->post(route('security.update'), [
            'no_wa'                 => '081234567890',
            'password'              => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ]);

        $response->assertSessionHas('success');

        $user->refresh();
        $siswa->refresh();

        $this->assertEquals('+6281234567890', $user->no_wa);
        $this->assertEquals('+6281234567890', $siswa->no_telepon);
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue(Hash::check('Rahasia123', $user->password));
        $this->assertFalse($user->isDefaultPassword());
        $this->assertFalse($user->needsSecurityUpdate());
    }

    public function test_user_cannot_reuse_default_password(): void
    {
        [$user] = $this->createSiswaUser();

        $response = $this->actingAs($user)->post(route('security.update'), [
            'no_wa'                 => '081234567890',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->isDefaultPassword());
    }

    public function test_security_modal_and_banner_appear_on_dashboard_when_needed(): void
    {
        [$user] = $this->createSiswaUser();

        $response = $this->actingAs($user)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Amankan Akun Anda');
        $response->assertSee('Buka Portal SiPintu');
        $response->assertDontSee('Cek Status dari SiPintu');
        $response->assertSee('Amankan Akun Sekarang');
    }

    public function test_security_modal_does_not_appear_when_already_secured(): void
    {
        [$user] = $this->createSiswaUser([
            'password' => Hash::make('CustomSecure123'),
            'no_wa'    => '+6281234567890',
            'password_changed_at' => now(),
        ]);

        $this->assertFalse($user->needsSecurityUpdate());

        $response = $this->actingAs($user)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertDontSee('Amankan Akun Sekarang');
        $response->assertDontSee('Buka Portal SiPintu');
    }

    public function test_user_can_check_sipintu_status_when_already_safe(): void
    {
        [$user] = $this->createSiswaUser([
            'password' => Hash::make('CustomSecure123'),
            'no_wa'    => '+6281234567890',
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('security.check-sipintu'));
        $response->assertSessionHas('success');
    }

    public function test_check_sipintu_returns_reminder_info_when_still_default(): void
    {
        [$user] = $this->createSiswaUser([
            'password' => Hash::make('password'),
            'no_wa'    => '+6281234567890',
            'password_changed_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('security.check-sipintu'));
        $response->assertSessionHas('info');
    }

    public function test_guru_with_default_password_sees_modal_and_can_update(): void
    {
        $guruUser = User::create([
            'name'     => 'Guru Uji',
            'email'    => 'guru_uji@sch.id',
            'role'     => 'guru',
            'nis_nip'  => '198501012010011002',
            'password' => Hash::make('password'),
        ]);

        $guru = \App\Models\Guru::create([
            'user_id'         => $guruUser->id,
            'nip'             => '198501012010011002',
            'nama_lengkap'    => 'Guru Uji, S.Kom.',
            'mata_pelajaran'  => 'Informatika',
            'no_telepon'      => null,
            'status_aktif'    => true,
        ]);

        $this->assertTrue($guruUser->needsSecurityUpdate());

        $response = $this->actingAs($guruUser)->get(route('guru.dashboard'));
        $response->assertOk();
        $response->assertSee('Amankan Akun Anda');
        $response->assertSee('Buka Portal SiPintu');

        // Update password and phone
        $postResponse = $this->actingAs($guruUser)->post(route('security.update'), [
            'no_wa'                 => '085678901234',
            'password'              => 'GuruBaru2026',
            'password_confirmation' => 'GuruBaru2026',
        ]);

        $postResponse->assertSessionHas('success');

        $guruUser->refresh();
        $guru->refresh();

        $this->assertEquals('+6285678901234', $guruUser->no_wa);
        $this->assertEquals('+6285678901234', $guru->no_telepon);
        $this->assertFalse($guruUser->needsSecurityUpdate());
    }
}
