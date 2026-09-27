<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SatpamScanVerifyTest extends TestCase
{
    use RefreshDatabase;

    private Dispensasi $dispensasi;
    private User $satpam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->satpam = User::create([
            'name'     => 'Satpam Test',
            'email'    => 'satpam@sch.id',
            'role'     => 'satpam',
            'password' => Hash::make('password'),
        ]);

        $jurusan = Jurusan::create(['nama_jurusan' => 'RPL', 'kode_jurusan' => 'RPL']);
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan_id' => $jurusan->id]);
        $siswaUser = User::create([
            'name'     => 'Siswa Test',
            'email'    => 'siswa@sch.id',
            'role'     => 'siswa',
            'password' => Hash::make('password'),
        ]);
        $siswa = Siswa::create([
            'user_id'      => $siswaUser->id,
            'nama_lengkap' => 'Siswa Test',
            'kelas_id'     => $kelas->id,
            'jurusan_id'   => $jurusan->id,
        ]);

        $this->dispensasi = Dispensasi::create([
            'siswa_id'            => $siswa->id,
            'nomor_surat'         => 'TEST/'.now()->format('YmdHis').'/'.random_int(1000, 9999),
            'kategori'            => 'izin',
            'alasan'              => 'Uji otomatis endpoint scan',
            'tujuan'              => 'Rumah sakit (uji)',
            'jam_keluar'          => 'Jam Pelajaran ke-1',
            'jam_kembali'         => 'Jam Pelajaran ke-2',
            'batas_waktu_kembali' => now()->addHour(),
            'status'              => 'disetujui',
            'qr_token'            => str_repeat('t', 64),
        ]);
    }

    public function test_verify_with_valid_token_returns_success(): void
    {
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $this->dispensasi->qr_token]),
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('dispensasi', ['id' => $this->dispensasi->id, 'status' => 'keluar']);
    }

    public function test_verify_with_url_payload_from_struk_returns_success(): void
    {
        // ✅ PERBAIKAN: Tambahkan actingAs agar terautentikasi sebagai satpam
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => url('/verifikasi/' . $this->dispensasi->id . '?token=' . $this->dispensasi->qr_token),
            ]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_verify_with_unknown_token_returns_404_with_success_false(): void
    {
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => str_repeat('x', 64),
            ]);

        $response->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_qr_keluar_menghasilkan_audit_log(): void
    {
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $this->dispensasi->qr_token]),
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('dispensasi', ['id' => $this->dispensasi->id, 'status' => 'keluar']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id'    => $this->satpam->id,
            'action'     => 'qr_keluar',
            'table_name' => 'dispensasi',
            'record_id'  => $this->dispensasi->id,
        ]);
    }

    public function test_qr_kembali_menghasilkan_audit_log_dan_menyelesaikan_dispensasi(): void
    {
        $this->dispensasi->update([
            'status'              => 'keluar',
            'waktu_keluar_aktual' => now(),
            'satpam_keluar_id'    => $this->satpam->id,
        ]);

        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $this->dispensasi->qr_token]),
            ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('dispensasi', ['id' => $this->dispensasi->id, 'status' => 'selesai']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id'    => $this->satpam->id,
            'action'     => 'qr_kembali',
            'table_name' => 'dispensasi',
            'record_id'  => $this->dispensasi->id,
        ]);
    }

    public function test_satpam_wa_contacted_berfungsi_dan_mengubah_flag(): void
    {
        $this->dispensasi->update([
            'status'              => 'keluar',
            'waktu_keluar_aktual' => now(),
            'satpam_keluar_id'    => $this->satpam->id,
            'is_warned'           => false,
        ]);

        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/dispensasi/' . $this->dispensasi->id . '/wa-contacted');

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('dispensasi', [
            'id'        => $this->dispensasi->id,
            'is_warned' => true,
        ]);
    }

    public function test_detail_view_approval_menggunakan_approved_at_dan_rejection_menggunakan_rejected_at(): void
    {
        $guruUser = User::create([
            'name'     => 'Guru Piket Test',
            'email'    => 'guru@sch.id',
            'role'     => 'guru',
            'password' => Hash::make('password'),
        ]);
        $guru = \App\Models\Guru::create([
            'user_id'      => $guruUser->id,
            'nama_lengkap' => 'Guru Piket Test',
            'nip'          => '198501012010011001',
            'status_aktif' => true,
        ]);

        $approvedTime = now()->subHours(2);
        $this->dispensasi->update([
            'guru_id'     => $guru->id,
            'approved_at' => $approvedTime,
            'status'      => 'disetujui',
        ]);

        $response = $this->actingAs($this->satpam)
            ->get('/satpam/dispensasi/' . $this->dispensasi->id . '/detail');

        $response->assertOk();
        $response->assertSee($approvedTime->isoFormat('D MMMM Y, HH:mm'));

        $rejectedTime = now()->subHour();
        $this->dispensasi->update([
            'rejected_at' => $rejectedTime,
            'status'      => 'ditolak',
        ]);

        $responseRejected = $this->actingAs($this->satpam)
            ->get('/satpam/dispensasi/' . $this->dispensasi->id . '/detail');

        $responseRejected->assertOk();
        $responseRejected->assertSee($rejectedTime->isoFormat('D MMMM Y, HH:mm'));
        $responseRejected->assertSee('Ditolak Pada');
    }

    public function test_satpam_konfirmasi_manual_keluar_dan_kembali(): void
    {
        // 1. Manual konfirmasi keluar
        $responseKeluar = $this->actingAs($this->satpam)
            ->post('/satpam/konfirmasi/' . $this->dispensasi->id . '/keluar');

        $responseKeluar->assertRedirect();
        $this->assertDatabaseHas('dispensasi', [
            'id'     => $this->dispensasi->id,
            'status' => 'keluar',
        ]);

        // 2. Manual konfirmasi kembali
        $responseKembali = $this->actingAs($this->satpam)
            ->post('/satpam/konfirmasi/' . $this->dispensasi->id . '/kembali');

        $responseKembali->assertRedirect();
        $this->assertDatabaseHas('dispensasi', [
            'id'     => $this->dispensasi->id,
            'status' => 'selesai',
        ]);
    }
}
