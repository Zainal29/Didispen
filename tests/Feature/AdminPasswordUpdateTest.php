<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_password_successfully(): void
    {
        $admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@sch.id',
            'role'     => 'admin',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($admin)->put(route('profil.update-password'), [
            'current_password'          => 'oldpassword123',
            'new_password'              => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('newpassword123', $admin->fresh()->password));
    }

    public function test_admin_cannot_update_password_with_incorrect_current_password(): void
    {
        $admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admin@sch.id',
            'role'     => 'admin',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($admin)->put(route('profil.update-password'), [
            'current_password'          => 'wrongpassword',
            'new_password'              => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('oldpassword123', $admin->fresh()->password));
    }

    public function test_non_admin_cannot_update_admin_password(): void
    {
        $siswa = User::create([
            'name'     => 'Siswa Test',
            'email'    => 'siswa@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($siswa)->put(route('profil.update-password'), [
            'current_password'          => 'oldpassword123',
            'new_password'              => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertForbidden();
    }
}
