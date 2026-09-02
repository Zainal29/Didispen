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
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => url('/verifikasi/'.$this->dispensasi->id),
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
}
