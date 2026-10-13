<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Dispensasi;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteAndLoginHistoryTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_can_soft_delete_restore_and_force_delete_dispensasi(): void
    {
        $admin = User::create([
            'name' => 'Admin Tester',
            'email' => 'admintest@sch.id',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $userSiswa = User::create([
            'name' => 'Siswa Tester',
            'email' => 'siswatest@sch.id',
            'role' => 'siswa',
            'password' => bcrypt('password'),
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'nis_nip' => '99999999',
            'nama_lengkap' => 'Siswa Tester',
            'status_aktif' => true,
        ]);

        $dispensasi = Dispensasi::create([
            'siswa_id' => $siswa->id,
            'nomor_surat' => 'DISP/TEST/001',
            'kategori' => 'izin',
            'alasan' => 'Alasan izin pengujian soft delete',
            'tujuan' => 'Rumah',
            'jam_keluar' => '08:00',
            'jam_kembali' => '10:00',
            'status' => 'menunggu',
        ]);

        // 1. Soft Delete via Admin
        $response = $this->actingAs($admin)->delete(route('admin.semua.pengajuan.destroy', $dispensasi));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('dispensasi', ['id' => $dispensasi->id]);

        // 2. Restore via Admin
        $response = $this->actingAs($admin)->post(route('admin.semua.pengajuan.restore', $dispensasi->id));
        $response->assertSessionHas('success');

        $this->assertNotSoftDeleted('dispensasi', ['id' => $dispensasi->id]);

        // 3. Force Delete via Admin
        $response = $this->actingAs($admin)->delete(route('admin.semua.pengajuan.force-delete', $dispensasi->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('dispensasi', ['id' => $dispensasi->id]);
    }

    public function test_user_can_clear_their_login_history(): void
    {
        $user = User::create([
            'name' => 'User Tester',
            'email' => 'usertest@sch.id',
            'role' => 'guru',
            'password' => bcrypt('password'),
        ]);

        // Buat dummy audit log login
        $log = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'table_name' => 'users',
            'record_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'device_type' => 'Laptop',
            'os' => 'Linux',
            'browser' => 'Chrome',
        ]);

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id]);

        // Test clear all login history
        $response = $this->actingAs($user)->delete(route('profil.clear-login-history'));
        $response->assertRedirect(route('profil.show'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('audit_logs', ['id' => $log->id]);
    }

    public function test_user_can_delete_single_login_history(): void
    {
        $user = User::create([
            'name' => 'User Tester 2',
            'email' => 'usertest2@sch.id',
            'role' => 'satpam',
            'password' => bcrypt('password'),
        ]);

        $log = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'table_name' => 'users',
            'record_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'device_type' => 'Laptop',
            'os' => 'Linux',
            'browser' => 'Chrome',
        ]);

        $response = $this->actingAs($user)->delete(route('profil.destroy-login-history', $log->id));
        $response->assertRedirect(route('profil.show'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('audit_logs', ['id' => $log->id]);
    }

    public function test_admin_can_delete_single_audit_log(): void
    {
        $admin = User::create([
            'name' => 'Admin Tester 3',
            'email' => 'admintest3@sch.id',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $log = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'table_name' => 'users',
            'record_id' => $admin->id,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.audit.destroy', $log->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('audit_logs', ['id' => $log->id]);
    }

    public function test_admin_can_clear_audit_logs(): void
    {
        $admin = User::create([
            'name' => 'Admin Tester 4',
            'email' => 'admintest4@sch.id',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $log1 = AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'table_name' => 'users',
            'record_id' => $admin->id,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.audit.clear', ['type' => 'login_only']));
        $response->assertRedirect(route('admin.audit.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('audit_logs', ['id' => $log1->id]);
    }
}
