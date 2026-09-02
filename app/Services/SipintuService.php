<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SipintuService
{
    public function __construct(
        private SipintuApiService $apiService,
        private AuditLogService   $auditLog
    ) {}

    /**
     * Helper untuk mengekstrak dan memproses password dari payload SiPintu API
     * Default password: "password"
     */
    private function resolvePasswordHash(array $item, string $defaultPlaintext = 'password'): string
    {
        $raw = data_get($item, 'password_hash')
            ?? data_get($item, 'password')
            ?? data_get($item, 'pass')
            ?? data_get($item, 'password_default')
            ?? data_get($item, 'user.password_hash')
            ?? data_get($item, 'user.password');

        if (filled($raw)) {
            $raw = (string) $raw;
            // Jika dari API sudah berupa Hash (Bcrypt/Argon2)
            if (preg_match('/^\$2[ayb]\$|\$argon2/i', $raw)) {
                return $raw;
            }
            // Jika dari API dalam bentuk plaintext, hash dengan Bcrypt
            return Hash::make($raw);
        }

        // Default password: "password"
        return Hash::make($defaultPlaintext);
    }

    /**
     * Sinkronisasi data Siswa dari SiPintu API Gateway
     * Optimized untuk 2,300+ siswa dengan batch processing
     */
    public function syncSiswa(bool $force = false): array
    {
        try {
            set_time_limit(600); // 10 menit timeout
            ini_set('memory_limit', '512M'); // Memory limit lebih tinggi

            Log::info('Memulai sinkronisasi siswa...');

            $apiResult = $this->apiService->getSiswaData($force);

            if ($apiResult['status'] === 'error') {
                return [
                    'success' => false,
                    'message' => $apiResult['message'] ?? 'Gagal terhubung ke API SiPintu Gateway.',
                    'stats'   => ['total' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []],
                ];
            }

            $studentsData = $apiResult['data'] ?? [];

            if (empty($studentsData)) {
                return [
                    'success' => true,
                    'message' => 'Koneksi SiPintu berhasil, namun tidak ada data siswa yang ditemukan.',
                    'stats'   => ['total' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []],
                ];
            }

            $totalStudents = count($studentsData);
            Log::info("Total siswa yang akan diproses: {$totalStudents}");

            $stats = [
                'total'    => $totalStudents,
                'inserted' => 0,
                'updated'  => 0,
                'failed'   => 0,
                'skipped'  => 0,
                'errors'   => [],
            ];

            DB::disableQueryLog();

            // ✅ PROSES PER BATCH (100 siswa per batch untuk efisiensi)
            $batchSize    = 100;
            $batches      = array_chunk($studentsData, $batchSize);
            $totalBatches = count($batches);

            foreach ($batches as $batchIndex => $batch) {
                Log::info("Processing batch " . ($batchIndex + 1) . "/{$totalBatches} ({$batchSize} siswa)");

                DB::transaction(function () use ($batch, &$stats) {
                    foreach ($batch as $index => $item) {
                        if (! is_array($item)) {
                            $stats['failed']++;
                            $stats['errors'][] = "Item index {$index} bukan format data yang valid.";
                            continue;
                        }

                        // Cek status alumni / tidak aktif
                        $statusAktif = $item['status_aktif']
                            ?? $item['is_active']
                            ?? $item['aktif']
                            ?? $item['active']
                            ?? true;

                        $isAlumni = $item['alumni']
                            ?? $item['is_alumni']
                            ?? (strtolower((string) ($item['status'] ?? '')) === 'alumni')
                            ?? false;

                        if ($isAlumni || ! $statusAktif) {
                            $stats['skipped']++;
                            continue;
                        }

                        $nis   = trim((string) ($item['nis'] ?? $item['nis_nip'] ?? $item['nisn'] ?? ''));
                        $nama  = trim($item['nama_lengkap'] ?? $item['name'] ?? $item['nama'] ?? '');
                        $email = $nis ? strtolower($nis . '@smkn1bangsri.sch.id') : '';

                        if (! $nis || ! $nama) {
                            $stats['failed']++;
                            $stats['errors'][] = "Baris " . ($index + 1) . ": NIS atau Nama kosong.";
                            continue;
                        }

                        try {
                            // 1. Resolve Jurusan & Kelas dari API SiPintu
                            $kelasData   = $item['kelas'] ?? $item['classroom'] ?? $item['nama_kelas'] ?? $item['rombel'] ?? '';
                            $jurusanData = $item['jurusan'] ?? $item['nama_jurusan'] ?? $item['kode_jurusan'] ?? (is_array($kelasData) ? ($kelasData['jurusan'] ?? $kelasData['major'] ?? '') : '');

                            $namaKelasDariApi    = trim(is_array($kelasData) ? ($kelasData['nama'] ?? $kelasData['nama_kelas'] ?? $kelasData['name'] ?? '') : $kelasData);
                            $kodeJurusanDariApi  = trim(is_array($jurusanData) ? ($jurusanData['kode'] ?? $jurusanData['kode_jurusan'] ?? $jurusanData['code'] ?? '') : ($item['kode_jurusan'] ?? ''));
                            $namaJurusanDariApi  = trim(is_array($jurusanData) ? ($jurusanData['nama'] ?? $jurusanData['nama_jurusan'] ?? $jurusanData['name'] ?? '') : $jurusanData);

                            // Tebak kode jurusan jika hanya ada nama kelas
                            if (! $namaJurusanDariApi && preg_match('/^(?:XII|XI|X)\s+([A-Z0-9]+)/i', $namaKelasDariApi, $kodeKelas)) {
                                $kodeJurusanDariApi  = strtoupper($kodeKelas[1]);
                                $namaJurusanDariApi  = [
                                    'PPLG' => 'Pengembangan Perangkat Lunak dan Gim',
                                    'MPLB' => 'Manajemen Perkantoran dan Layanan Bisnis',
                                    'PM'   => 'Pemasaran',
                                    'AKL'  => 'Akuntansi Keuangan Lembaga',
                                    'TO'   => 'Teknik Otomotif',
                                ][$kodeJurusanDariApi] ?? $kodeJurusanDariApi;
                            }

                            // Cari / Buat Jurusan
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

                            // Cari / Buat Kelas
                            $kelasObj = null;
                            if ($namaKelasDariApi && $jurusanObj) {
                                $kelasQuery = Kelas::whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower($namaKelasDariApi)])
                                    ->where('jurusan_id', $jurusanObj->id);
                                $kelasObj = $kelasQuery->first();

                                if (! $kelasObj) {
                                    $kelasObj = Kelas::whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower($namaKelasDariApi)])->first();
                                    if ($kelasObj) {
                                        $kelasObj->update(['jurusan_id' => $jurusanObj->id]);
                                    }
                                }

                                $kelasObj ??= Kelas::create([
                                    'nama_kelas' => $namaKelasDariApi,
                                    'jurusan_id' => $jurusanObj->id,
                                    'tingkat'    => preg_match('/^(XII|XI|X)\b/i', $namaKelasDariApi, $tingkat) ? strtoupper($tingkat[1]) : 'X',
                                ]);
                            }

                            $kelasFinal     = $kelasObj;
                            $jurusanIdFinal = $kelasFinal?->jurusan_id ?? $jurusanObj?->id;

                            // Ekstraksi No Telepon
                            $nomorTelepon = data_get($item, 'no_telepon')
                                ?? data_get($item, 'hp')
                                ?? data_get($item, 'telepon')
                                ?? data_get($item, 'no_telp')
                                ?? data_get($item, 'no_hp')
                                ?? data_get($item, 'nomor_telepon')
                                ?? data_get($item, 'nomor_hp')
                                ?? data_get($item, 'phone')
                                ?? data_get($item, 'phone_number')
                                ?? data_get($item, 'whatsapp')
                                ?? data_get($item, 'wa')
                                ?? data_get($item, 'mobile')
                                ?? data_get($item, 'user.phone')
                                ?? data_get($item, 'user.no_telepon');

                            if ($nomorTelepon) {
                                $nomorTelepon = preg_replace('/[^0-9+]/', '', trim((string) $nomorTelepon));
                            }

                            // Ekstraksi Alamat
                            $alamat = data_get($item, 'alamat')
                                ?? data_get($item, 'address')
                                ?? data_get($item, 'alamat_lengkap')
                                ?? data_get($item, 'full_address')
                                ?? data_get($item, 'domisili')
                                ?? null;

                            $tanggalLahir     = $item['tanggal_lahir'] ?? $item['tgl_lahir'] ?? null;
                            $statusAktifFinal = isset($item['status_aktif']) ? (bool) $item['status_aktif'] : true;

                            // Process Password (default: "password")
                            $passwordHash = $this->resolvePasswordHash($item, 'password');

                            // Upsert User
                            $user = User::where('nis_nip', $nis)->orWhere('email', $email)->first();

                            if ($user) {
                                $user->update([
                                    'name'     => $nama,
                                    'email'    => $email,
                                    'nis_nip'  => $nis,
                                    'password' => $passwordHash,
                                    'role'     => 'siswa',
                                ]);
                                $stats['updated']++;
                            } else {
                                $user = User::create([
                                    'name'     => $nama,
                                    'email'    => $email,
                                    'password' => $passwordHash,
                                    'role'     => 'siswa',
                                    'nis_nip'  => $nis,
                                ]);
                                $stats['inserted']++;
                            }

                            // Upsert Siswa
                            $siswa = Siswa::firstOrNew(['user_id' => $user->id]);

                            $siswaData = [
                                'nis_nip'      => $nis,
                                'jurusan_id'   => $jurusanIdFinal,
                                'kelas_id'     => $kelasFinal?->id,
                                'nama_lengkap' => $nama,
                                'status_aktif' => $statusAktifFinal,
                            ];

                            if (filled($tanggalLahir)) {
                                $siswaData['tanggal_lahir'] = $tanggalLahir;
                            }

                            if (filled($alamat)) {
                                $siswaData['alamat'] = $alamat;
                            }

                            if (filled($nomorTelepon)) {
                                $siswaData['no_telepon'] = $nomorTelepon;
                            }

                            $siswa->fill($siswaData);
                            $siswa->save();

                        } catch (\Throwable $e) {
                            $stats['failed']++;
                            $stats['errors'][] = "NIS {$nis} ({$nama}): " . $e->getMessage();
                            Log::error("Err sync Siswa NIS {$nis}: " . $e->getMessage());
                        }
                    }
                });

                // Beri jeda antar batch agar server tidak overload
                if ($batchIndex < $totalBatches - 1) {
                    usleep(500000); // 0.5 detik
                    gc_collect_cycles(); // Garbage collection
                }
            }

            // Clean up duplicate master kelas
            Kelas::withCount('siswa')->get()
                ->groupBy(fn (Kelas $kelas) => $kelas->jurusan_id . '|' . strtolower(trim($kelas->nama_kelas)))
                ->filter(fn ($group) => $group->count() > 1)
                ->each(function ($duplicates) {
                    $utama = $duplicates->sortByDesc('siswa_count')->first();

                    foreach ($duplicates as $duplikat) {
                        if ($duplikat->id === $utama->id) {
                            continue;
                        }

                        Siswa::where('kelas_id', $duplikat->id)->update([
                            'kelas_id'   => $utama->id,
                            'jurusan_id' => $utama->jurusan_id,
                        ]);
                        $duplikat->delete();
                    }
                });

            // Clean up orphan classes/majors
            Kelas::whereDoesntHave('siswa')->delete();
            Jurusan::whereDoesntHave('kelas')->delete();

            if (auth()->check()) {
                $this->auditLog->log(auth()->id(), 'sync_sipintu_siswa', 'siswa', null);
            }

            $summaryMsg = "Sinkronisasi Siswa Selesai: Total {$stats['total']} data ({$stats['inserted']} baru, {$stats['updated']} diperbarui, {$stats['failed']} gagal, {$stats['skipped']} alumni/tidak aktif dilewati).";
            Log::info($summaryMsg, $stats);

            return [
                'success' => true,
                'message' => $summaryMsg,
                'stats'   => $stats,
            ];

        } catch (\Throwable $e) {
            Log::error('SiPintu Sync Siswa Exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses sinkronisasi SiPintu: ' . $e->getMessage(),
                'stats'   => ['total' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => [$e->getMessage()]],
            ];
        }
    }

    /**
     * Sinkronisasi data Guru dari SiPintu API Gateway
     * Optimized untuk ~72 guru
     */
    public function syncGuru(bool $force = false): array
    {
        try {
            set_time_limit(300); // 5 menit timeout
            ini_set('memory_limit', '256M');

            Log::info('Memulai sinkronisasi guru...');

            $apiResult = $this->apiService->getGuruData($force);

            if ($apiResult['status'] === 'error') {
                return [
                    'success' => false,
                    'message' => $apiResult['message'] ?? 'Gagal terhubung ke API SiPintu Gateway.',
                    'stats'   => ['total' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []],
                ];
            }

            $teachersData = $apiResult['data'] ?? [];

            if (empty($teachersData)) {
                return [
                    'success' => true,
                    'message' => 'Koneksi SiPintu berhasil, namun tidak ada data guru yang ditemukan.',
                    'stats'   => ['total' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []],
                ];
            }

            $totalGuru = count($teachersData);
            Log::info("Total guru yang akan diproses: {$totalGuru}");

            $stats = [
                'total'    => $totalGuru,
                'inserted' => 0,
                'updated'  => 0,
                'failed'   => 0,
                'errors'   => [],
            ];

            DB::disableQueryLog();

            DB::transaction(function () use ($teachersData, &$stats) {
                foreach ($teachersData as $index => $item) {
                    if (! is_array($item)) {
                        $stats['failed']++;
                        $stats['errors'][] = "Item index {$index} bukan format data yang valid.";
                        continue;
                    }

                    $nip = trim((string) (
                        $item['nip']
                        ?? $item['nis_nip']
                        ?? $item['nip_guru']
                        ?? $item['no_induk']
                        ?? $item['employee_id']
                        ?? $item['id_pegawai']
                        ?? ''
                    ));
                    $nama = trim($item['nama_lengkap'] ?? $item['name'] ?? $item['nama'] ?? '');

                    // Guru tanpa NIP (honorer) tetap bisa sync pakai NIP placeholder
                    if (! $nip) {
                        $apiId = $item['id'] ?? $item['guru_id'] ?? null;
                        $nip   = 'HONOR-' . ($apiId ?? ($index + 1));
                    }

                    $email = strtolower($nip . '@smkn1bangsri.sch.id');

                    if (! $nama) {
                        $stats['failed']++;
                        $stats['errors'][] = "Baris " . ($index + 1) . ": Nama kosong.";
                        continue;
                    }

                    try {
                        // Extract Mata Pelajaran
                        $rawMapel = $item['mata_pelajaran'] ?? $item['mapel'] ?? null;
                        if (is_array($rawMapel)) {
                            $mapel = implode(', ', array_filter(array_map('trim', $rawMapel)));
                        } else {
                            $mapel = filled($rawMapel) ? trim((string) $rawMapel) : null;
                        }

                        // Alias No HP Guru
                        $hp = data_get($item, 'no_telepon')
                            ?? data_get($item, 'hp')
                            ?? data_get($item, 'telepon')
                            ?? data_get($item, 'phone')
                            ?? data_get($item, 'whatsapp')
                            ?? data_get($item, 'mobile')
                            ?? null;

                        if ($hp) {
                            $hp = preg_replace('/[^0-9+]/', '', trim((string) $hp));
                        }

                        // Alias Alamat Guru
                        $alamat = data_get($item, 'alamat')
                            ?? data_get($item, 'address')
                            ?? data_get($item, 'alamat_lengkap')
                            ?? null;

                        $tanggalLahir = $item['tanggal_lahir'] ?? $item['tgl_lahir'] ?? null;
                        $statusAktif  = isset($item['status_aktif']) ? (bool) $item['status_aktif'] : true;

                        // Process Password (default: "password")
                        $passwordHash = $this->resolvePasswordHash($item, 'password');

                        // Upsert User
                        $user = User::where('nis_nip', $nip)->orWhere('email', $email)->first();

                        if ($user) {
                            $user->update([
                                'name'     => $nama,
                                'email'    => $email,
                                'nis_nip'  => $nip,
                                'password' => $passwordHash,
                                'role'     => 'guru',
                            ]);
                            $stats['updated']++;
                        } else {
                            $user = User::create([
                                'name'     => $nama,
                                'email'    => $email,
                                'password' => $passwordHash,
                                'role'     => 'guru',
                                'nis_nip'  => $nip,
                            ]);
                            $stats['inserted']++;
                        }

                        // Upsert Guru
                        $guru     = Guru::firstOrNew(['user_id' => $user->id]);
                        $guruData = [
                            'nip'           => $nip,
                            'nama_lengkap'  => $nama,
                            'tanggal_lahir' => $tanggalLahir,
                            'status_aktif'  => $statusAktif,
                        ];

                        if (filled($mapel)) {
                            $guruData['mata_pelajaran'] = $mapel;
                        } elseif (! $guru->exists) {
                            $guruData['mata_pelajaran'] = 'Umum';
                        }

                        if (filled($hp)) {
                            $guruData['no_telepon'] = $hp;
                        }

                        if (filled($alamat)) {
                            $guruData['alamat'] = $alamat;
                        }

                        $guru->fill($guruData);
                        $guru->save();

                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        $stats['errors'][] = "NIP {$nip} ({$nama}): " . $e->getMessage();
                        Log::error("Err sync Guru NIP {$nip}: " . $e->getMessage());
                    }
                }

                if (auth()->check()) {
                    $this->auditLog->log(auth()->id(), 'sync_sipintu_guru', 'guru', null);
                }
            });

            $summaryMsg = "Sinkronisasi Guru Selesai: Total {$stats['total']} data ({$stats['inserted']} baru, {$stats['updated']} diperbarui, {$stats['failed']} gagal).";
            Log::info($summaryMsg, $stats);

            return [
                'success' => true,
                'message' => $summaryMsg,
                'stats'   => $stats,
            ];

        } catch (\Throwable $e) {
            Log::error('SiPintu Sync Guru Exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses sinkronisasi SiPintu: ' . $e->getMessage(),
                'stats'   => ['total' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => [$e->getMessage()]],
            ];
        }
    }
}
