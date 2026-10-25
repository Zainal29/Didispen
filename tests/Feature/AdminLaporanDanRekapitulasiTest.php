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

class AdminLaporanDanRekapitulasiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin_' . rand(1000, 9999) . '@sch.id',
            'role' => 'admin',
            'nis_nip' => 'ADM' . rand(100, 999),
            'password' => Hash::make('password'),
        ]);
    }

    private function createSampleData(): array
    {
        $jurusan = Jurusan::create([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kode_jurusan' => 'RPL',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XII RPL 1',
            'jurusan_id' => $jurusan->id,
            'tingkat' => 'XII',
        ]);

        $userSiswa1 = User::create([
            'name' => 'Siswa 1',
            'email' => 'siswa1@sch.id',
            'role' => 'siswa',
            'nis_nip' => '1001',
            'password' => Hash::make('password'),
        ]);

        $siswa1 = Siswa::create([
            'user_id' => $userSiswa1->id,
            'kelas_id' => $kelas->id,
            'nis' => '1001',
            'nama_lengkap' => 'Siswa Satu',
            'status_aktif' => true,
        ]);

        $userGuru = User::create([
            'name' => 'Guru Piket',
            'email' => 'guru@sch.id',
            'role' => 'guru',
            'nis_nip' => 'G101',
            'password' => Hash::make('password'),
        ]);

        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => 'G101',
            'nama_lengkap' => 'Guru Piket S.Pd',
            'status_aktif' => true,
        ]);

        return compact('jurusan', 'kelas', 'siswa1', 'guru');
    }

    public function test_admin_can_access_laporan_index_with_tidak_scan_keluar_filter(): void
    {
        $admin = $this->createAdminUser();
        $data = $this->createSampleData();

        // 1. Dispensasi kadaluarsa tapi TIDAK scan keluar (waktu_keluar_aktual null)
        $dTidakScanKeluar = Dispensasi::create([
            'nomor_surat' => 'DISP-001',
            'siswa_id' => $data['siswa1']->id,
            'guru_id' => $data['guru']->id,
            'kategori' => 'keperluan_pribadi',
            'alasan' => 'Beli alat tulis',
            'tujuan' => 'Fotokopi depan',
            'jam_keluar' => '08:00',
            'jam_kembali' => '09:00',
            'sampai_pulang' => false,
            'status' => 'kadaluarsa',
            'waktu_keluar_aktual' => null,
            'waktu_kembali_aktual' => null,
            'catatan_admin' => 'Kadaluarsa otomatis karena tidak melakukan check-out pada tanggal izin.',
        ]);

        // 2. Dispensasi kadaluarsa yang SEMPAT scan keluar tetapi TIDAK scan kembali
        $dTidakKembali = Dispensasi::create([
            'nomor_surat' => 'DISP-002',
            'siswa_id' => $data['siswa1']->id,
            'guru_id' => $data['guru']->id,
            'kategori' => 'keperluan_pribadi',
            'alasan' => 'Izin ke bank',
            'tujuan' => 'Bank Jatim',
            'jam_keluar' => '09:00',
            'jam_kembali' => '10:00',
            'sampai_pulang' => false,
            'status' => 'kadaluarsa',
            'waktu_keluar_aktual' => now()->subHours(2),
            'waktu_kembali_aktual' => null,
            'catatan_admin' => 'Kadaluarsa otomatis: Siswa tidak melakukan scan kembali hingga KBM berakhir.',
        ]);

        // 3. Dispensasi selesai normal
        $dSelesai = Dispensasi::create([
            'nomor_surat' => 'DISP-003',
            'siswa_id' => $data['siswa1']->id,
            'guru_id' => $data['guru']->id,
            'kategori' => 'dinas_sekolah',
            'alasan' => 'Lomba',
            'tujuan' => 'Disdik',
            'jam_keluar' => '08:00',
            'jam_kembali' => '11:00',
            'sampai_pulang' => false,
            'status' => 'selesai',
            'waktu_keluar_aktual' => now()->subHours(3),
            'waktu_kembali_aktual' => now()->subHours(1),
            'batas_waktu_kembali' => now()->subHours(1)->addMinutes(10),
        ]);

        // Akses filter status = tidak_scan_keluar
        $response = $this->actingAs($admin)->get(route('admin.laporan.index', ['status' => 'tidak_scan_keluar']));

        $response->assertStatus(200);
        $response->assertSee('DISP-001');
        $response->assertDontSee('DISP-002');
        $response->assertDontSee('DISP-003');
        $response->assertSee('Kadaluarsa (Tidak Scan Keluar');
        $response->assertSee('Tidak Scan Keluar');
    }

    public function test_admin_can_export_pdf_and_excel_with_tidak_scan_keluar_filter(): void
    {
        $admin = $this->createAdminUser();
        $data = $this->createSampleData();

        Dispensasi::create([
            'nomor_surat' => 'DISP-EXP-001',
            'siswa_id' => $data['siswa1']->id,
            'guru_id' => $data['guru']->id,
            'kategori' => 'keperluan_pribadi',
            'alasan' => 'Beli buku',
            'tujuan' => 'Toko buku',
            'jam_keluar' => '08:00',
            'jam_kembali' => '09:00',
            'sampai_pulang' => false,
            'status' => 'kadaluarsa',
            'waktu_keluar_aktual' => null,
            'waktu_kembali_aktual' => null,
        ]);

        // Test Export PDF
        $responsePdf = $this->actingAs($admin)->get(route('admin.laporan.pdf', ['status' => 'tidak_scan_keluar']));
        $responsePdf->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $responsePdf->headers->get('Content-Type') ?? '');

        // Test Export Excel
        $responseExcel = $this->actingAs($admin)->get(route('admin.laporan.excel', ['status' => 'tidak_scan_keluar']));
        $responseExcel->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $responseExcel->headers->get('Content-Type') ?? '');
    }
}
