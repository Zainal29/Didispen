<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SipintuService
{
    private string $baseUrl;

    private string $clientId;

    private string $clientSecret;

    public function __construct(private AuditLogService $auditLog)
    {
        $this->baseUrl = rtrim(config('services.sipintu.url', 'http://localhost:8000'), '/');
        $this->clientId = config('services.sipintu.client_id', 'app_muzl3or17cqw');
        $this->clientSecret = config('services.sipintu.client_secret', 'sec_YNN09nBXzK1Hp1siNe0D48s9ZxprIxiW');
    }

    /**
     * Kirim request HTTP ke SiPintu Gateway dengan Server-to-Server Auth Headers
     */
    private function request(string $endpoint, array $queryParams = [], int $timeout = 60)
    {
        $url = $this->baseUrl.'/'.ltrim($endpoint, '/');

        return Http::withHeaders([
            'X-Client-ID' => $this->clientId,
            'X-Client-Secret' => $this->clientSecret,
            'Accept' => 'application/json',
        ])->timeout($timeout)->get($url, $queryParams);
    }

    /**
     * Sinkronisasi data Siswa dari SiPintu Gateway
     */
    public function syncSiswa(): array
    {
        try {
            set_time_limit(900);

            $response = $this->request('/api/v1/sijuna/students');

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke SiPintu Gateway (Status: '.$response->status().'). '.($response->json('message') ?? 'Pastikan server SiPintu aktif.'),
                    'count' => 0,
                ];
            }

            $raw = $response->json();
            $studentsData = $raw['data'] ?? $raw['students'] ?? (is_array($raw) ? $raw : []);

            if (empty($studentsData)) {
                return [
                    'success' => true,
                    'message' => 'Koneksi SiPintu berhasil, namun tidak ada data siswa yang ditemukan.',
                    'count' => 0,
                ];
            }

            // Endpoint daftar kadang mengembalikan classroom null, sedangkan endpoint detail memilikinya.
            foreach ($studentsData as $index => $student) {
                if (! is_array($student) || ! empty($student['classroom']) || empty($student['id'])) {
                    continue;
                }

                try {
                    $detailResponse = $this->request('/api/v1/sijuna/students/'.$student['id'], [], 15);
                    if ($detailResponse->successful() && is_array($detailResponse->json('data'))) {
                        $studentsData[$index] = array_replace($student, $detailResponse->json('data'));
                    }
                } catch (ConnectionException $e) {
                    // Classroom detail boleh gagal; data siswa lainnya tetap diproses.
                    Log::warning('Gagal mengambil classroom detail Sipintu', [
                        'student_id' => $student['id'],
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            // 🔍 DEBUG: Lihat struktur data asli dari API (Hapus atau komentari baris ini setelah berhasil)
            // Log::info('Sample Data Siswa dari API:', [collect($studentsData)->first()]);

            $syncedCount = 0;
            $defaultPassword = Hash::make('password123');

            DB::disableQueryLog();

            DB::transaction(function () use ($studentsData, $defaultPassword, &$syncedCount) {
                foreach ($studentsData as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $nis = trim((string) ($item['nis'] ?? $item['nis_nip'] ?? $item['nisn'] ?? ''));
                    $nama = trim($item['nama_lengkap'] ?? $item['name'] ?? $item['nama'] ?? '');
                    $email = $nis ? strtolower($nis.'@smkn1bangsri.sch.id') : '';

                    if (! $nis || ! $nama) {
                        continue;
                    }

                    // Resolve jurusan lebih dulu agar kelas selalu terikat pada jurusan dari Sipintu.
                    $kelasData = $item['classroom'] ?? $item['kelas'] ?? $item['nama_kelas'] ?? $item['rombel'] ?? '';
                    $jurusanData = $item['jurusan'] ?? $item['nama_jurusan'] ?? $item['kode_jurusan'] ?? (is_array($kelasData) ? ($kelasData['jurusan'] ?? $kelasData['major'] ?? '') : '');
                    $namaKelasDariApi = trim(is_array($kelasData) ? ($kelasData['nama'] ?? $kelasData['nama_kelas'] ?? $kelasData['name'] ?? '') : $kelasData);
                    $kodeJurusanDariApi = trim(is_array($jurusanData) ? ($jurusanData['kode'] ?? $jurusanData['kode_jurusan'] ?? $jurusanData['code'] ?? '') : ($item['kode_jurusan'] ?? ''));
                    $namaJurusanDariApi = trim(is_array($jurusanData) ? ($jurusanData['nama'] ?? $jurusanData['nama_jurusan'] ?? $jurusanData['name'] ?? '') : $jurusanData);

                    if (! $namaJurusanDariApi && preg_match('/^(?:XII|XI|X)\s+([A-Z0-9]+)/i', $namaKelasDariApi, $kodeKelas)) {
                        $kodeJurusanDariApi = strtoupper($kodeKelas[1]);
                        $namaJurusanDariApi = [
                            'PPLG' => 'Pengembangan Perangkat Lunak dan Gim',
                            'MPLB' => 'Manajemen Perkantoran dan Layanan Bisnis',
                            'PM' => 'Pemasaran',
                            'AKL' => 'Akuntansi Keuangan Lembaga',
                            'TO' => 'Teknik Otomotif',
                        ][$kodeJurusanDariApi] ?? $kodeJurusanDariApi;
                    }
                    $jurusanObj = null;
                    if ($kodeJurusanDariApi) {
                        $jurusanObj = Jurusan::whereRaw('LOWER(TRIM(kode_jurusan)) = ?', [strtolower($kodeJurusanDariApi)])->first();
                    }
                    if (! $jurusanObj && $namaJurusanDariApi) {
                        $jurusanObj = Jurusan::whereRaw('LOWER(TRIM(nama_jurusan)) = ?', [strtolower($namaJurusanDariApi)])->first();
                    }
                    if (! $jurusanObj && ($namaJurusanDariApi || $kodeJurusanDariApi)) {
                        $jurusanObj = Jurusan::create([
                            'kode_jurusan' => strtoupper($kodeJurusanDariApi ?: substr(preg_replace('/[^A-Za-z]/', '', $namaJurusanDariApi), 0, 5)) ?: 'UMUM',
                            'nama_jurusan' => $namaJurusanDariApi ?: $kodeJurusanDariApi,
                        ]);
                    }
                    $kelasObj = null;
                    if ($namaKelasDariApi && $jurusanObj) {
                        $kelasQuery = Kelas::whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower($namaKelasDariApi)]);
                        if ($jurusanObj) {
                            $kelasQuery->where('jurusan_id', $jurusanObj->id);
                        }
                        $kelasObj = $kelasQuery->first();

                        // Perbaiki data lama yang memiliki nama kelas benar tetapi jurusan salah.
                        if (! $kelasObj && $jurusanObj) {
                            $kelasObj = Kelas::whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower($namaKelasDariApi)])->first();
                            if ($kelasObj) {
                                $kelasObj->update(['jurusan_id' => $jurusanObj->id]);
                            }
                        }
                        $kelasObj ??= Kelas::create([
                            'nama_kelas' => $namaKelasDariApi,
                            'jurusan_id' => $jurusanObj->id,
                            'tingkat' => preg_match('/^(XII|XI|X)\b/i', $namaKelasDariApi, $tingkat) ? strtoupper($tingkat[1]) : 'X',
                        ]);
                    }

                    // Jangan menebak kelas pertama jika Sipintu tidak mengirim classroom.
                    $kelasFinal = $kelasObj;
                    $jurusanIdFinal = $kelasFinal?->jurusan_id ?? $jurusanObj?->id;

                    // 4. Update atau Buat User
                    $user = User::where('nis_nip', $nis)->orWhere('email', $email)->first();

                    if ($user) {
                        $user->update([
                            'name' => $nama,
                            'email' => $email,
                            'nis_nip' => $nis,
                            'role' => 'siswa',
                        ]);
                    } else {
                        $user = User::create([
                            'name' => $nama,
                            'email' => $email,
                            'password' => $defaultPassword,
                            'role' => 'siswa',
                            'nis_nip' => $nis,
                        ]);
                    }

                    // 5. Update atau Buat Siswa (jurusan_id DIJAMIN terisi)
                    Siswa::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'jurusan_id' => $jurusanIdFinal,
                            'kelas_id' => $kelasFinal?->id,
                            'nama_lengkap' => $nama,
                            'tanggal_lahir' => $item['tanggal_lahir'] ?? $item['tgl_lahir'] ?? null,
                            'alamat' => $item['alamat'] ?? null,
                            'no_telepon' => $item['no_telepon'] ?? $item['telepon'] ?? null,
                        ]
                    );

                    $syncedCount++;
                }

                // Gabungkan master kelas duplikat dengan nama dan jurusan yang sama.
                Kelas::withCount('siswa')->get()
                    ->groupBy(fn (Kelas $kelas) => $kelas->jurusan_id.'|'.strtolower(trim($kelas->nama_kelas)))
                    ->filter(fn ($group) => $group->count() > 1)
                    ->each(function ($duplicates) {
                        $utama = $duplicates->sortByDesc('siswa_count')->first();

                        foreach ($duplicates as $duplikat) {
                            if ($duplikat->id === $utama->id) {
                                continue;
                            }

                            Siswa::where('kelas_id', $duplikat->id)->update([
                                'kelas_id' => $utama->id,
                                'jurusan_id' => $utama->jurusan_id,
                            ]);
                            $duplikat->delete();
                        }
                    });

                // Bersihkan data kelas/jurusan lama yang sudah tidak dipakai setelah relasi siswa diperbarui.
                Kelas::whereDoesntHave('siswa')->delete();
                Jurusan::whereDoesntHave('kelas')->delete();

                if (auth()->check()) {
                    $this->auditLog->log(auth()->id(), 'sync_sipintu_siswa', 'siswa', null);
                }
            });

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$syncedCount} data siswa beserta kelas dan jurusan dari SiPintu Gateway.",
                'count' => $syncedCount,
            ];
        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke SiPintu Gateway ('.$this->baseUrl.'). Pastikan aplikasi SiPintu Gateway telah di-run dan URL di .env sudah sesuai.',
                'count' => 0,
            ];
        } catch (\Exception $e) {
            Log::error('SiPintu Sync Siswa Error: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses sinkronisasi SiPintu: '.$e->getMessage(),
                'count' => 0,
            ];
        }
    }

    /**
     * Sinkronisasi data Guru dari SiPintu Gateway
     */
    public function syncGuru(): array
    {
        try {
            set_time_limit(300);

            $response = $this->request('/api/v1/sijuna/teachers');

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Gagal terhubung ke SiPintu Gateway (Status: '.$response->status().'). Pastikan server SiPintu aktif.',
                    'count' => 0,
                ];
            }

            $raw = $response->json();
            $teachersData = $raw['data'] ?? $raw['teachers'] ?? (is_array($raw) ? $raw : []);

            if (empty($teachersData)) {
                return [
                    'success' => true,
                    'message' => 'Koneksi SiPintu berhasil, namun tidak ada data guru yang ditemukan.',
                    'count' => 0,
                ];
            }

            $syncedCount = 0;
            $defaultPassword = Hash::make('password123');

            DB::disableQueryLog();

            DB::transaction(function () use ($teachersData, $defaultPassword, &$syncedCount) {
                foreach ($teachersData as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $nip = trim((string) ($item['nip'] ?? $item['nis_nip'] ?? ''));
                    $nama = $item['nama_lengkap'] ?? $item['name'] ?? $item['nama'] ?? null;
                    $email = $nip ? strtolower($nip.'@smkn1bangsri.sch.id') : null;
                    $mapel = $item['mata_pelajaran'] ?? $item['mapel'] ?? 'Umum';

                    if (! $nip || ! $nama) {
                        continue;
                    }

                    // Update atau Buat User
                    $user = User::where('nis_nip', $nip)
                        ->orWhere('email', $email)
                        ->first();

                    if ($user) {
                        $user->update([
                            'name' => $nama,
                            'email' => $email,
                            'nis_nip' => $nip,
                            'role' => 'guru',
                        ]);
                    } else {
                        $user = User::create([
                            'name' => $nama,
                            'email' => $email,
                            'password' => $defaultPassword,
                            'role' => 'guru',
                            'nis_nip' => $nip,
                        ]);
                    }

                    // Update atau Buat Guru
                    Guru::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'nip' => $nip,
                            'nama_lengkap' => $nama,
                            'mata_pelajaran' => $mapel,
                        ]
                    );

                    $syncedCount++;
                }

                if (auth()->check()) {
                    $this->auditLog->log(auth()->id(), 'sync_sipintu_guru', 'guru', null);
                }
            });

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$syncedCount} data guru dari SiPintu Gateway.",
                'count' => $syncedCount,
            ];
        } catch (ConnectionException $e) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke SiPintu Gateway ('.$this->baseUrl.'). Pastikan aplikasi SiPintu Gateway telah di-run (misal: php artisan serve --port=8001) dan variabel SIPINTU_API_URL di file .env sudah sesuai.',
                'count' => 0,
            ];
        } catch (\Exception $e) {
            Log::error('SiPintu Sync Guru Error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses sinkronisasi SiPintu: '.$e->getMessage(),
                'count' => 0,
            ];
        }
    }
}
