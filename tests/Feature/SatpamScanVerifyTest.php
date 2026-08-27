<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SatpamScanVerifyTest extends TestCase
{
    private Dispensasi $dispensasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->satpam = User::where('role', 'satpam')->firstOrFail();

        $this->dispensasi = Dispensasi::create([
            'siswa_id' => Dispensasi::first()->siswa_id,
            'nomor_surat' => 'TEST/'.now()->format('YmdHis').'/'.random_int(1000, 9999),
            'kategori' => 'izin',
            'alasan' => 'Uji otomatis endpoint scan',
            'tujuan' => 'Rumah sakit (uji)',
            'jam_keluar' => 'Jam Pelajaran ke-1',
            'jam_kembali' => 'Jam Pelajaran ke-2',
            'batas_waktu_kembali' => now()->addHour(),
            'status' => 'disetujui',
            'qr_token' => str_repeat('t', 64),
        ]);
    }

    protected function tearDown(): void
    {
        $this->dispensasi->delete();

        parent::tearDown();
    }

    public function test_verify_with_valid_token_returns_success(): void
    {
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => json_encode(['token' => $this->dispensasi->qr_token]),
            ]);

        dump(['status' => $response->status(), 'success' => $response->json('success')]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('dispensasi', ['id' => $this->dispensasi->id, 'status' => 'keluar']);
    }

    public function test_verify_with_url_payload_from_struk_returns_success(): void
    {
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => url('/verifikasi/'.$this->dispensasi->id),
            ]);

        dump(['status' => $response->status(), 'success' => $response->json('success')]);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_verify_with_unknown_token_returns_200_with_success_false(): void
    {
        $response = $this->actingAs($this->satpam)
            ->postJson('/satpam/scan/verify', [
                'qr_data' => str_repeat('x', 64),
            ]);

        dump(['status' => $response->status(), 'success' => $response->json('success')]);

        $response->assertOk()->assertJson(['success' => false]);
    }
}
