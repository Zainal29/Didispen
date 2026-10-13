<?php

namespace Tests\Feature;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\JadwalPiketGuru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SiswaHubungiGuruPiketMultiTest extends TestCase
{
    use RefreshDatabase;

    protected User $siswaUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusan = Jurusan::create([
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'kode_jurusan' => 'RPL',
        ]);

        $this->kelas = Kelas::create([
            'nama_kelas' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan_id' => $this->jurusan->id,
        ]);

        $unique = rand(100000, 999999);
        $this->siswaUser = User::create([
            'name' => 'Siswa Penguji ' . $unique,
            'email' => "siswa_{$unique}@sch.id",
            'role' => 'siswa',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nama_lengkap' => 'Siswa Penguji ' . $unique,
            'nis_nip' => (string) $unique,
            'kelas_id' => $this->kelas->id,
            'jurusan_id' => $this->jurusan->id,
            'status_aktif' => true,
        ]);
    }

    private function createGuru(string $nama, string $noWa = '081234567890'): array
    {
        $unique = rand(100000, 999999);
        $user = User::create([
            'name' => $nama,
            'email' => "guru_{$unique}@sch.id",
            'role' => 'guru',
            'nis_nip' => (string) $unique,
            'password' => Hash::make('password'),
        ]);

        $guru = Guru::create([
            'user_id' => $user->id,
            'nama_lengkap' => $nama,
            'nip' => (string) $unique,
            'no_telepon' => $noWa,
            'status_aktif' => true,
        ]);

        return [$user, $guru];
    }

    private function createJadwal(int $hari, string $jamMulai, string $jamSelesai): JadwalPiket
    {
        return JadwalPiket::create([
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'tanggal_mulai_berlaku' => '2026-01-01',
            'is_aktif' => true,
        ]);
    }

    public function test_modal_menampilkan_seluruh_guru_piket_yang_bertugas_pada_sesi_aktif(): void
    {
        // Set waktu ke hari Senin jam 08:00 (hari = 1)
        Carbon::setTestNow(Carbon::parse('2026-09-28 08:00:00', 'Asia/Jakarta'));

        // Jadwal sesi 1 (07:00 - 09:30)
        $jadwalSesi1 = $this->createJadwal(1, '07:00:00', '09:30:00');

        // Buat 2 guru piket untuk Sesi 1
        [$user1, $guru1] = $this->createGuru('Guru Piket Sesi 1 A', '081111111111');
        [$user2, $guru2] = $this->createGuru('Guru Piket Sesi 1 B', '082222222222');

        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSesi1->id, 'guru_id' => $guru1->id]);
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSesi1->id, 'guru_id' => $guru2->id]);

        // Buat Sesi 2 (09:30 - 12:00) dengan guru lain
        $jadwalSesi2 = $this->createJadwal(1, '09:30:00', '12:00:00');
        [$user3, $guru3] = $this->createGuru('Guru Piket Sesi 2 C', '083333333333');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSesi2->id, 'guru_id' => $guru3->id]);

        // Siswa membuat pengajuan yang berstatus 'menunggu'
        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->siswa->id,
            'nomor_surat' => 'DISP/2026/09/001',
            'status' => 'menunggu',
            'alasan' => 'Urusan keluarga mendesak',
            'tujuan' => 'Rumah',
            'kategori' => 'keluarga',
            'jam_keluar' => '08:00',
            'jam_kembali' => '09:00',
            'created_at' => now('Asia/Jakarta'),
        ]);

        // 1. Dashboard Siswa: Menampilkan kedua guru sesi 1, dan TIDAK menampilkan guru sesi 2
        $dashboardResponse = $this->actingAs($this->siswaUser)->get(route('siswa.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Guru Piket Sesi 1 A');
        $dashboardResponse->assertSee('Guru Piket Sesi 1 B');
        $dashboardResponse->assertDontSee('Guru Piket Sesi 2 C');

        // 2. Detail Pengajuan Siswa: Menampilkan kedua guru sesi 1, dan TIDAK menampilkan guru sesi 2
        $detailResponse = $this->actingAs($this->siswaUser)->get(route('siswa.pengajuan.show', $dispensasi));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Guru Piket Sesi 1 A');
        $detailResponse->assertSee('Guru Piket Sesi 1 B');
        $detailResponse->assertDontSee('Guru Piket Sesi 2 C');

        // 3. Klik WhatsApp khusus untuk guru 2 mengarahkan link ke nomor guru 2
        $waResponse = $this->actingAs($this->siswaUser)->get(route('siswa.pengajuan.hubungi-guru-piket', [
            'dispensasi' => $dispensasi,
            'guru_id' => $guru2->id,
        ]));
        $waResponse->assertRedirect();
        $this->assertStringContainsString('6282222222222', $waResponse->headers->get('Location'));
    }

    public function test_modal_berganti_guru_saat_sesi_berpindah(): void
    {
        // Sesi 1 (07:00 - 09:30)
        $jadwalSesi1 = $this->createJadwal(1, '07:00:00', '09:30:00');
        [$user1, $guru1] = $this->createGuru('Bapak Guru Sesi Pagi', '081111111111');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSesi1->id, 'guru_id' => $guru1->id]);

        // Sesi 2 (09:30 - 12:00)
        $jadwalSesi2 = $this->createJadwal(1, '09:30:00', '12:00:00');
        [$user2, $guru2] = $this->createGuru('Ibu Guru Sesi Siang', '082222222222');
        JadwalPiketGuru::create(['jadwal_piket_id' => $jadwalSesi2->id, 'guru_id' => $guru2->id]);

        // Waktu berpindah ke 10:00 (Sesi Siang)
        Carbon::setTestNow(Carbon::parse('2026-09-28 10:00:00', 'Asia/Jakarta'));

        $dispensasi = Dispensasi::create([
            'siswa_id' => $this->siswa->id,
            'nomor_surat' => 'DISP/2026/09/002',
            'status' => 'menunggu',
            'alasan' => 'Keperluan dinas',
            'tujuan' => 'Kantor Dinas',
            'kategori' => 'keperluan_sekolah',
            'jam_keluar' => '10:00',
            'jam_kembali' => '11:00',
            'created_at' => now('Asia/Jakarta'),
        ]);

        $detailResponse = $this->actingAs($this->siswaUser)->get(route('siswa.pengajuan.show', $dispensasi));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Ibu Guru Sesi Siang');
        $detailResponse->assertDontSee('Bapak Guru Sesi Pagi');
    }
}
