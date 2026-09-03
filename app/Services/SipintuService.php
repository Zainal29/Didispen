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
        private AuditLogService $auditLog
    ) {}

    /**
     * Resolve password dari API.
     */
    private function resolvePasswordHash(
        array $item,
        string $defaultPlaintext = 'password'
    ): string {
        $raw = data_get($item, 'password_hash')
            ?? data_get($item, 'password')
            ?? data_get($item, 'pass')
            ?? data_get($item, 'password_default')
            ?? data_get($item, 'user.password_hash')
            ?? data_get($item, 'user.password');

        if (filled($raw)) {
            $raw = (string) $raw;

            if (preg_match('/^\$2[ayb]\$|\$argon2/i', $raw)) {
                return $raw;
            }

            return Hash::make($raw);
        }

        return Hash::make($defaultPlaintext);
    }

    /**
     * Ubah berbagai format menjadi boolean.
     */
    private function toBoolean(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 1;
        }

        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return null;
        }

        if (in_array($value, [
            '1',
            'true',
            'aktif',
            'active',
            'ya',
            'yes',
            'terdaftar',
        ], true)) {
            return true;
        }

        if (in_array($value, [
            '0',
            'false',
            'tidak',
            'no',
            'nonaktif',
            'tidak aktif',
            'inactive',
            'alumni',
            'lulus',
            'keluar',
            'berhenti',
        ], true)) {
            return false;
        }

        return null;
    }

    /**
     * Ekstrak nama kelas dari payload data siswa.
     */
    private function extractNamaKelas(array $item): string
    {
        $kelasData = data_get($item, 'kelas')
            ?? data_get($item, 'classroom')
            ?? data_get($item, 'nama_kelas')
            ?? data_get($item, 'rombel')
            ?? data_get($item, 'nama_rombel')
            ?? data_get($item, 'student.kelas')
            ?? data_get($item, 'student.nama_kelas')
            ?? data_get($item, 'siswa.kelas')
            ?? data_get($item, 'data.kelas')
            ?? '';

        if (is_array($kelasData)) {
            return trim((string) (
                $kelasData['nama']
                ?? $kelasData['nama_kelas']
                ?? $kelasData['name']
                ?? $kelasData['rombel']
                ?? ''
            ));
        }

        return trim((string) $kelasData);
    }

    /**
     * ==========================================================
     * CEK SISWA AKTIF (FAIL-CLOSED)
     * ==========================================================
     *
     * Aturan Validasi Ketat:
     * 1. Cek kelengkapan NIS dan Nama.
     * 2. Cek flag boolean alumni / is_alumni (Root & Nested) -> Jika true, tolak langsung.
     * 3. Cek field status teks (status, status_siswa, status_pendidikan, student_status, dll).
     * 4. Validasi kelas: tidak boleh kosong, tidak boleh berstatus alumni/lulus,
     *    dan harus memiliki pattern tingkat kelas valid (X, XI, atau XII).
     * 5. Cek flag status aktif boolean (status_aktif, is_active, dll).
     * 6. FAIL-CLOSED: Jika tidak ada bukti status aktif yang valid, tolak data.
     */
    private function isStudentActive(array $item, ?string &$reason = null): bool
    {
        $reason = 'Status aktif tidak dapat diverifikasi (FAIL-CLOSED)';

        /*
         * ----------------------------------------------------------
         * 1. IDENTITAS DASAR
         * ----------------------------------------------------------
         */
        $nis = trim((string) (
            $item['nis']
            ?? $item['nis_nip']
            ?? $item['nisn']
            ?? data_get($item, 'student.nis')
            ?? data_get($item, 'data.nis')
            ?? ''
        ));

        $nama = trim((string) (
            $item['nama_lengkap']
            ?? $item['name']
            ?? $item['nama']
            ?? data_get($item, 'student.nama')
            ?? data_get($item, 'data.nama')
            ?? ''
        ));

        if ($nis === '' || $nama === '') {
            $reason = 'NIS atau Nama kosong';
            return false;
        }

        /*
         * ----------------------------------------------------------
         * 2. PRIORITAS 1: FLAG ALUMNI (ROOT & NESTED)
         * ----------------------------------------------------------
         */
        $alumniKeys = [
            'alumni',
            'is_alumni',
            'status_alumni',
            'isAlumni',
            'is_lulus',
            'lulus',
            'student.alumni',
            'student.is_alumni',
            'student.status_alumni',
            'siswa.alumni',
            'siswa.is_alumni',
            'peserta_didik.alumni',
            'peserta_didik.is_alumni',
            'data.alumni',
            'data.is_alumni',
        ];

        foreach ($alumniKeys as $key) {
            $val = data_get($item, $key);
            if ($val !== null) {
                $isAlumni = $this->toBoolean($val);
                if ($isAlumni === true) {
                    $reason = 'Field alumni = true';
                    return false;
                }
            }
        }

        /*
         * ----------------------------------------------------------
         * 3. PRIORITAS 2: STATUS TEKS (ROOT & NESTED)
         * ----------------------------------------------------------
         */
        $statusFields = [
            'status',
            'status_siswa',
            'student_status',
            'status_peserta_didik',
            'status_keaktifan',
            'keaktifan',
            'status_pendidikan',
            'status_belajar',
            'keterangan_status',
            'keterangan',
            'student.status',
            'student.student_status',
            'student.status_pendidikan',
            'siswa.status',
            'peserta_didik.status',
            'data.status',
            'data.student_status',
            'data.status_pendidikan',
        ];

        $nonActiveKeywords = [
            'alumni',
            'lulus',
            'lulusan',
            'tidak aktif',
            'nonaktif',
            'non aktif',
            'non-aktif',
            'inactive',
            'keluar',
            'berhenti',
            'dikeluarkan',
            'drop out',
            'do',
            'pindah',
            'meninggal',
            'mutasi',
            'alumnus',
            'tamat',
        ];

        $activeKeywords = [
            'aktif',
            'active',
            'siswa aktif',
            'terdaftar',
            'masih aktif',
            'mengikuti pembelajaran',
            'belajar',
        ];

        $hasExplicitActiveText = false;
        $hasUnknownStatusText = false;
        $unknownStatusSample = '';

        foreach ($statusFields as $field) {
            $val = data_get($item, $field);
            if ($val === null || is_array($val)) {
                continue;
            }

            $rawText = trim((string) $val);
            $statusText = strtolower($rawText);

            if ($statusText === '') {
                continue;
            }

            // Cek jika status adalah nonaktif/alumni
            if (in_array($statusText, $nonActiveKeywords, true)) {
                $reason = "Status: {$rawText}";
                return false;
            }

            foreach ($nonActiveKeywords as $badKeyword) {
                if ($statusText === $badKeyword || str_starts_with($statusText, $badKeyword . ' ') || str_ends_with($statusText, ' ' . $badKeyword)) {
                    $reason = "Status: {$rawText}";
                    return false;
                }
            }

            // Cek jika status teks positif
            if (in_array($statusText, $activeKeywords, true)) {
                $hasExplicitActiveText = true;
            } else {
                $hasUnknownStatusText = true;
                $unknownStatusSample = $rawText;
            }
        }

        if ($hasUnknownStatusText && ! $hasExplicitActiveText) {
            $reason = "Status tidak valid / tidak dikenal: {$unknownStatusSample}";
            return false;
        }

        /*
         * ----------------------------------------------------------
         * 4. PRIORITAS 3: VALIDASI KELAS (X, XI, XII)
         * ----------------------------------------------------------
         */
        $namaKelas = $this->extractNamaKelas($item);

        if ($namaKelas === '') {
            $reason = 'Kelas kosong atau tidak terdaftar';
            return false;
        }

        $lowerKelas = strtolower($namaKelas);
        foreach (['alumni', 'lulus', 'keluar', 'nonaktif', 'non aktif', 'drop out', 'do', 'pindah', 'mutasi'] as $badWord) {
            if (str_contains($lowerKelas, $badWord)) {
                $reason = "Kelas: {$namaKelas}";
                return false;
            }
        }

        // Kelas harus memiliki tingkat valid: X, XI, atau XII
        if (! preg_match('/^(XII|XI|X)(?:\s+|-|_|\.|\/|$)/i', $namaKelas)) {
            $reason = "Tingkat kelas tidak valid (bukan X, XI, XII): {$namaKelas}";
            return false;
        }

        /*
         * ----------------------------------------------------------
         * 5. PRIORITAS 4: STATUS AKTIF BOOLEAN (ROOT & NESTED)
         * ----------------------------------------------------------
         */
        $statusAktifFields = [
            'status_aktif',
            'is_active',
            'aktif',
            'active',
            'status_active',
            'aktif_status',
            'student.status_aktif',
            'student.is_active',
            'siswa.status_aktif',
            'siswa.is_active',
            'peserta_didik.status_aktif',
            'peserta_didik.is_active',
            'data.status_aktif',
            'data.is_active',
        ];

        $hasExplicitActiveBool = false;

        foreach ($statusAktifFields as $field) {
            $val = data_get($item, $field);
            if ($val !== null) {
                $bool = $this->toBoolean($val);
                if ($bool === false) {
                    $reason = "Field {$field} = false";
                    return false;
                }
                if ($bool === true) {
                    $hasExplicitActiveBool = true;
                }
            }
        }

        /*
         * ----------------------------------------------------------
         * 6. EVALUASI AKHIR (FAIL-CLOSED)
         * ----------------------------------------------------------
         */
        if ($hasExplicitActiveBool || $hasExplicitActiveText || $namaKelas !== '') {
            $reason = 'Aktif';
            return true;
        }

        $reason = 'Status aktif tidak dapat dikonfirmasi (FAIL-CLOSED)';
        return false;
    }

    /**
     * ==========================================================
     * SINKRONISASI SISWA AKTIF
     * ==========================================================
     */
    public function syncSiswa(): array
    {
        try {
            set_time_limit(600);
            ini_set('memory_limit', '512M');

            Log::info('==========================================');
            Log::info('Memulai sinkronisasi SISWA AKTIF...');
            Log::info('==========================================');

            $apiResult = $this->apiService->getSiswaData();

            if (($apiResult['status'] ?? null) === 'error') {
                return [
                    'success' => false,
                    'message' => $apiResult['message']
                        ?? 'Gagal terhubung ke API SiPintu Gateway.',
                    'stats' => [
                        'total' => 0,
                        'inserted' => 0,
                        'updated' => 0,
                        'failed' => 0,
                        'skipped' => 0,
                        'errors' => [],
                    ],
                ];
            }

            $studentsData = $apiResult['data'] ?? [];

            if (
                ! is_array($studentsData)
                || empty($studentsData)
            ) {
                return [
                    'success' => true,
                    'message' =>
                        'Koneksi SiPintu berhasil, namun tidak ada data siswa yang ditemukan.',
                    'stats' => [
                        'total' => 0,
                        'inserted' => 0,
                        'updated' => 0,
                        'failed' => 0,
                        'skipped' => 0,
                        'errors' => [],
                    ],
                ];
            }

            /*
             * ======================================================
             * FILTER AWAL SISWA (HANYA SISWA AKTIF)
             * ======================================================
             */

            $jumlahDariApi = count($studentsData);
            $activeStudents = [];
            $jumlahDilewati = 0;

            foreach ($studentsData as $item) {
                if (! is_array($item)) {
                    $jumlahDilewati++;
                    continue;
                }

                $reason = '';
                $nis = trim((string) (
                    $item['nis']
                    ?? $item['nis_nip']
                    ?? $item['nisn']
                    ?? data_get($item, 'student.nis')
                    ?? '-'
                ));

                $nama = trim((string) (
                    $item['nama_lengkap']
                    ?? $item['name']
                    ?? $item['nama']
                    ?? data_get($item, 'student.nama')
                    ?? 'Tanpa Nama'
                ));

                if ($this->isStudentActive($item, $reason)) {
                    $activeStudents[] = $item;
                } else {
                    $jumlahDilewati++;
                    Log::info("SKIP: NIS {$nis} ({$nama}) - {$reason}");

                    // Cleanup / Deaktivasi data alumni yang sebelumnya sudah terlanjur ada di database lokal
                    if ($nis !== '' && $nis !== '-') {
                        $existingSiswa = Siswa::where('nis_nip', $nis)->first();
                        if ($existingSiswa) {
                            if ($existingSiswa->dispensasi()->doesntExist()) {
                                $userToDelete = $existingSiswa->user;
                                $existingSiswa->delete();
                                $userToDelete?->delete();
                            } else {
                                $existingSiswa->update(['status_aktif' => false]);
                            }
                        }
                    }
                }
            }

            $totalStudents = count($activeStudents);

            Log::info("Total data siswa dari API: {$jumlahDariApi}");
            Log::info("Total siswa AKTIF: {$totalStudents}");
            Log::info("Total data dilewati: {$jumlahDilewati}");

            if ($totalStudents === 0) {
                return [
                    'success' => true,
                    'message' =>
                        "API mengirim {$jumlahDariApi} data, tetapi tidak ada data yang lolos sebagai siswa aktif ({$jumlahDilewati} dilewati).",
                    'stats' => [
                        'total' => 0,
                        'inserted' => 0,
                        'updated' => 0,
                        'failed' => 0,
                        'skipped' => $jumlahDilewati,
                        'errors' => [],
                    ],
                ];
            }

            $stats = [
                'total' => $totalStudents,
                'inserted' => 0,
                'updated' => 0,
                'failed' => 0,
                'skipped' => $jumlahDilewati,
                'errors' => [],
            ];

            DB::disableQueryLog();

            /*
             * ======================================================
             * PROSES BATCH SISWA AKTIF
             * ======================================================
             */

            $batchSize = 100;
            $batches = array_chunk($activeStudents, $batchSize);
            $totalBatches = count($batches);

            foreach ($batches as $batchIndex => $batch) {

                Log::info(
                    'Processing siswa batch '
                    . ($batchIndex + 1)
                    . "/{$totalBatches} ("
                    . count($batch)
                    . ' siswa)'
                );

                DB::transaction(
                    function () use (
                        $batch,
                        &$stats
                    ) {
                        foreach ($batch as $index => $item) {

                            if (! is_array($item)) {
                                $stats['failed']++;
                                $stats['errors'][] =
                                    "Item index {$index} bukan format data yang valid.";
                                continue;
                            }

                            /*
                             * ==================================================
                             * IDENTITAS
                             * ==================================================
                             */

                            $nis = trim(
                                (string) (
                                    $item['nis']
                                    ?? $item['nis_nip']
                                    ?? $item['nisn']
                                    ?? data_get($item, 'student.nis')
                                    ?? ''
                                )
                            );

                            $nama = trim(
                                (string) (
                                    $item['nama_lengkap']
                                    ?? $item['name']
                                    ?? $item['nama']
                                    ?? data_get($item, 'student.nama')
                                    ?? ''
                                )
                            );

                            if (! $nis || ! $nama) {
                                $stats['failed']++;
                                $stats['errors'][] =
                                    'Baris '
                                    . ($index + 1)
                                    . ': NIS atau Nama kosong.';
                                continue;
                            }

                            $email = strtolower(
                                $nis
                                . '@smkn1bangsri.sch.id'
                            );

                            try {

                                /*
                                 * ==================================================
                                 * KELAS & JURUSAN
                                 * ==================================================
                                 */

                                $kelasData =
                                    $item['kelas']
                                    ?? $item['classroom']
                                    ?? $item['nama_kelas']
                                    ?? $item['rombel']
                                    ?? data_get($item, 'student.kelas')
                                    ?? '';

                                $jurusanData =
                                    $item['jurusan']
                                    ?? $item['nama_jurusan']
                                    ?? $item['kode_jurusan']
                                    ?? (
                                        is_array($kelasData)
                                            ? (
                                                $kelasData['jurusan']
                                                ?? $kelasData['major']
                                                ?? ''
                                            )
                                            : ''
                                    );

                                $namaKelasDariApi = $this->extractNamaKelas($item);

                                $kodeJurusanDariApi = trim(
                                    (string) (
                                        is_array($jurusanData)
                                            ? (
                                                $jurusanData['kode']
                                                ?? $jurusanData['kode_jurusan']
                                                ?? $jurusanData['code']
                                                ?? ''
                                            )
                                            : (
                                                $item['kode_jurusan']
                                                ?? ''
                                            )
                                    )
                                );

                                $namaJurusanDariApi = trim(
                                    (string) (
                                        is_array($jurusanData)
                                            ? (
                                                $jurusanData['nama']
                                                ?? $jurusanData['nama_jurusan']
                                                ?? $jurusanData['name']
                                                ?? ''
                                            )
                                            : $jurusanData
                                    )
                                );

                                /*
                                 * Tebak jurusan dari nama kelas jika kosong.
                                 */
                                if (
                                    ! $namaJurusanDariApi
                                    && preg_match(
                                        '/^(?:XII|XI|X)\s+([A-Z0-9]+)/i',
                                        $namaKelasDariApi,
                                        $kodeKelas
                                    )
                                ) {
                                    $kodeJurusanDariApi =
                                        strtoupper(
                                            $kodeKelas[1]
                                        );

                                    $namaJurusanDariApi =
                                        [
                                            'PPLG' =>
                                                'Pengembangan Perangkat Lunak dan Gim',
                                            'MPLB' =>
                                                'Manajemen Perkantoran dan Layanan Bisnis',
                                            'PM' =>
                                                'Pemasaran',
                                            'AKL' =>
                                                'Akuntansi Keuangan Lembaga',
                                            'TO' =>
                                                'Teknik Otomotif',
                                        ][$kodeJurusanDariApi]
                                        ?? $kodeJurusanDariApi;
                                }

                                /*
                                 * ==================================================
                                 * JURUSAN
                                 * ==================================================
                                 */

                                $jurusanObj = null;

                                if ($kodeJurusanDariApi) {
                                    $jurusanObj =
                                        Jurusan::whereRaw(
                                            'LOWER(TRIM(kode_jurusan)) = ?',
                                            [
                                                strtolower(
                                                    $kodeJurusanDariApi
                                                ),
                                            ]
                                        )->first();
                                }

                                if (
                                    ! $jurusanObj
                                    && $namaJurusanDariApi
                                ) {
                                    $jurusanObj =
                                        Jurusan::whereRaw(
                                            'LOWER(TRIM(nama_jurusan)) = ?',
                                            [
                                                strtolower(
                                                    $namaJurusanDariApi
                                                ),
                                            ]
                                        )->first();
                                }

                                if (
                                    ! $jurusanObj
                                    && (
                                        $namaJurusanDariApi
                                        || $kodeJurusanDariApi
                                    )
                                ) {
                                    $jurusanObj =
                                        Jurusan::create([
                                            'kode_jurusan' =>
                                                strtoupper(
                                                    $kodeJurusanDariApi
                                                    ?: substr(
                                                        preg_replace(
                                                            '/[^A-Za-z]/',
                                                            '',
                                                            $namaJurusanDariApi
                                                        ),
                                                        0,
                                                        5
                                                    )
                                                )
                                                ?: 'UMUM',

                                            'nama_jurusan' =>
                                                $namaJurusanDariApi
                                                ?: $kodeJurusanDariApi,
                                        ]);
                                }

                                /*
                                 * ==================================================
                                 * KELAS
                                 * ==================================================
                                 */

                                $kelasObj = null;

                                if (
                                    $namaKelasDariApi
                                    && $jurusanObj
                                ) {
                                    $kelasObj =
                                        Kelas::whereRaw(
                                            'LOWER(TRIM(nama_kelas)) = ?',
                                            [
                                                strtolower(
                                                    $namaKelasDariApi
                                                ),
                                            ]
                                        )
                                        ->where(
                                            'jurusan_id',
                                            $jurusanObj->id
                                        )
                                        ->first();

                                    if (! $kelasObj) {
                                        $kelasObj =
                                            Kelas::whereRaw(
                                                'LOWER(TRIM(nama_kelas)) = ?',
                                                [
                                                    strtolower(
                                                        $namaKelasDariApi
                                                    ),
                                                ],
                                            )->first();

                                        if ($kelasObj) {
                                            $kelasObj->update([
                                                'jurusan_id' =>
                                                    $jurusanObj->id,
                                            ]);
                                        }
                                    }

                                    if (! $kelasObj) {
                                        $tingkat = 'X';

                                        if (
                                            preg_match(
                                                '/^(XII|XI|X)\b/i',
                                                $namaKelasDariApi,
                                                $tingkatMatch
                                            )
                                        ) {
                                            $tingkat =
                                                strtoupper(
                                                    $tingkatMatch[1]
                                                );
                                        }

                                        $kelasObj =
                                            Kelas::create([
                                                'nama_kelas' =>
                                                    $namaKelasDariApi,

                                                'jurusan_id' =>
                                                    $jurusanObj->id,

                                                'tingkat' =>
                                                    $tingkat,
                                            ]);
                                    }
                                }

                                $kelasFinal = $kelasObj;

                                $jurusanIdFinal =
                                    $kelasFinal?->jurusan_id
                                    ?? $jurusanObj?->id;

                                /*
                                 * ==================================================
                                 * TELEPON
                                 * ==================================================
                                 */

                                $nomorTelepon =
                                    data_get(
                                        $item,
                                        'no_telepon'
                                    )
                                    ?? data_get(
                                        $item,
                                        'hp'
                                    )
                                    ?? data_get(
                                        $item,
                                        'telepon'
                                    )
                                    ?? data_get(
                                        $item,
                                        'no_telp'
                                    )
                                    ?? data_get(
                                        $item,
                                        'no_hp'
                                    )
                                    ?? data_get(
                                        $item,
                                        'nomor_telepon'
                                    )
                                    ?? data_get(
                                        $item,
                                        'nomor_hp'
                                    )
                                    ?? data_get(
                                        $item,
                                        'phone'
                                    )
                                    ?? data_get(
                                        $item,
                                        'phone_number'
                                    )
                                    ?? data_get(
                                        $item,
                                        'whatsapp'
                                    )
                                    ?? data_get(
                                        $item,
                                        'wa'
                                    )
                                    ?? data_get(
                                        $item,
                                        'mobile'
                                    )
                                    ?? data_get(
                                        $item,
                                        'user.phone'
                                    )
                                    ?? data_get(
                                        $item,
                                        'user.no_telepon'
                                    );

                                if ($nomorTelepon) {
                                    $nomorTelepon =
                                        preg_replace(
                                            '/[^0-9+]/',
                                            '',
                                            trim(
                                                (string) $nomorTelepon
                                            )
                                        );
                                }

                                /*
                                 * ==================================================
                                 * ALAMAT
                                 * ==================================================
                                 */

                                $alamat =
                                    data_get(
                                        $item,
                                        'alamat'
                                    )
                                    ?? data_get(
                                        $item,
                                        'address'
                                    )
                                    ?? data_get(
                                        $item,
                                        'alamat_lengkap'
                                    )
                                    ?? data_get(
                                        $item,
                                        'full_address'
                                    )
                                    ?? data_get(
                                        $item,
                                        'domisili'
                                    )
                                    ?? null;

                                /*
                                 * ==================================================
                                 * TANGGAL LAHIR
                                 * ==================================================
                                 */

                                $tanggalLahir =
                                    $item['tanggal_lahir']
                                    ?? $item['tgl_lahir']
                                    ?? null;

                                /*
                                 * ==================================================
                                 * PASSWORD
                                 * ==================================================
                                 */

                                $passwordHash =
                                    $this->resolvePasswordHash(
                                        $item,
                                        'password'
                                    );

                                /*
                                 * ==================================================
                                 * USER
                                 * ==================================================
                                 */

                                $user =
                                    User::where(
                                        'nis_nip',
                                        $nis
                                    )
                                    ->orWhere(
                                        'email',
                                        $email
                                    )
                                    ->first();

                                if ($user) {
                                    $user->update([
                                        'name' =>
                                            $nama,

                                        'email' =>
                                            $email,

                                        'nis_nip' =>
                                            $nis,

                                        'password' =>
                                            $passwordHash,

                                        'role' =>
                                            'siswa',
                                    ]);

                                    $stats['updated']++;
                                } else {
                                    $user =
                                        User::create([
                                            'name' =>
                                                $nama,

                                            'email' =>
                                                $email,

                                            'password' =>
                                                $passwordHash,

                                            'role' =>
                                                'siswa',

                                            'nis_nip' =>
                                                $nis,
                                        ]);

                                    $stats['inserted']++;
                                }

                                /*
                                 * ==================================================
                                 * SISWA
                                 * ==================================================
                                 */

                                $siswa =
                                    Siswa::firstOrNew([
                                        'user_id' =>
                                            $user->id,
                                    ]);

                                $siswaData = [
                                    'nis_nip' =>
                                        $nis,

                                    'jurusan_id' =>
                                        $jurusanIdFinal,

                                    'kelas_id' =>
                                        $kelasFinal?->id,

                                    'nama_lengkap' =>
                                        $nama,

                                    'status_aktif' =>
                                        true,
                                ];

                                if (filled($tanggalLahir)) {
                                    $siswaData[
                                        'tanggal_lahir'
                                    ] = $tanggalLahir;
                                }

                                if (filled($alamat)) {
                                    $siswaData[
                                        'alamat'
                                    ] = $alamat;
                                }

                                if (filled($nomorTelepon)) {
                                    $siswaData[
                                        'no_telepon'
                                    ] = $nomorTelepon;
                                }

                                $siswa->fill(
                                    $siswaData
                                );

                                $siswa->save();

                            } catch (\Throwable $e) {
                                $stats['failed']++;

                                $stats['errors'][] =
                                    "NIS {$nis} ({$nama}): "
                                    . $e->getMessage();

                                Log::error(
                                    "Err sync Siswa NIS {$nis}: "
                                    . $e->getMessage()
                                );
                            }
                        }
                    }
                );

                /*
                 * Jeda antar batch & Memory Management.
                 */
                if ($batchIndex < $totalBatches - 1) {
                    usleep(500000);
                }

                gc_collect_cycles();
            }

            /*
             * ==========================================================
             * CLEANUP DUPLIKAT KELAS
             * ==========================================================
             */

            Kelas::withCount('siswa')
                ->get()
                ->groupBy(
                    fn (Kelas $kelas) =>
                        $kelas->jurusan_id
                        . '|'
                        . strtolower(
                            trim(
                                $kelas->nama_kelas
                            )
                        )
                )
                ->filter(
                    fn ($group) =>
                        $group->count() > 1
                )
                ->each(
                    function ($duplicates) {

                        $utama =
                            $duplicates
                                ->sortByDesc(
                                    'siswa_count'
                                )
                                ->first();

                        foreach (
                            $duplicates
                            as $duplikat
                        ) {
                            if (
                                $duplikat->id
                                === $utama->id
                            ) {
                                continue;
                            }

                            Siswa::where(
                                'kelas_id',
                                $duplikat->id
                            )->update([
                                'kelas_id' =>
                                    $utama->id,

                                'jurusan_id' =>
                                    $utama->jurusan_id,
                            ]);

                            $duplikat->delete();
                        }
                    }
                );

            /*
             * Hapus kelas kosong.
             */
            Kelas::whereDoesntHave(
                'siswa'
            )->delete();

            /*
             * Hapus jurusan kosong.
             */
            Jurusan::whereDoesntHave(
                'kelas'
            )->delete();

            /*
             * ==========================================================
             * AUDIT LOG
             * ==========================================================
             */

            if (auth()->check()) {
                $this->auditLog->log(
                    auth()->id(),
                    'sync_sipintu_siswa',
                    'siswa',
                    null
                );
            }

            /*
             * ==========================================================
             * SUMMARY
             * ==========================================================
             */

            $summaryMsg =
                "Sinkronisasi Siswa Aktif Selesai: "
                . "Total {$stats['total']} siswa aktif "
                . "({$stats['inserted']} baru, "
                . "{$stats['updated']} diperbarui, "
                . "{$stats['failed']} gagal, "
                . "{$stats['skipped']} dilewati).";

            Log::info(
                $summaryMsg,
                $stats
            );

            return [
                'success' => true,
                'message' => $summaryMsg,
                'stats' => $stats,
            ];

        } catch (\Throwable $e) {

            Log::error(
                'SiPintu Sync Siswa Exception: '
                . $e->getMessage()
                . ' | Trace: '
                . $e->getTraceAsString()
            );

            return [
                'success' => false,
                'message' =>
                    'Terjadi kesalahan saat memproses '
                    . 'sinkronisasi SiPintu: '
                    . $e->getMessage(),
                'stats' => [
                    'total' => 0,
                    'inserted' => 0,
                    'updated' => 0,
                    'failed' => 0,
                    'skipped' => 0,
                    'errors' => [
                        $e->getMessage(),
                    ],
                ],
            ];
        }
    }

    /**
     * ==========================================================
     * SINKRONISASI GURU
     * ==========================================================
     */
    public function syncGuru(): array
    {
        try {
            set_time_limit(300);
            ini_set('memory_limit', '256M');

            Log::info('==========================================');
            Log::info('Memulai sinkronisasi guru...');
            Log::info('==========================================');

            $apiResult = $this->apiService->getGuruData();

            if (($apiResult['status'] ?? null) === 'error') {
                return [
                    'success' => false,
                    'message' => $apiResult['message']
                        ?? 'Gagal terhubung ke API SiPintu Gateway.',
                    'stats' => [
                        'total' => 0,
                        'inserted' => 0,
                        'updated' => 0,
                        'failed' => 0,
                        'skipped' => 0,
                        'errors' => [],
                    ],
                ];
            }

            $teachersData = $apiResult['data'] ?? [];

            if (
                ! is_array($teachersData)
                || empty($teachersData)
            ) {
                return [
                    'success' => true,
                    'message' =>
                        'Koneksi SiPintu berhasil, namun tidak ada data guru yang ditemukan.',
                    'stats' => [
                        'total' => 0,
                        'inserted' => 0,
                        'updated' => 0,
                        'failed' => 0,
                        'skipped' => 0,
                        'errors' => [],
                    ],
                ];
            }

            $totalGuru = count($teachersData);

            Log::info(
                "Total guru dari API: {$totalGuru}"
            );

            $stats = [
                'total' => $totalGuru,
                'inserted' => 0,
                'updated' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => [],
            ];

            DB::disableQueryLog();

            DB::transaction(
                function () use (
                    $teachersData,
                    &$stats
                ) {

                    foreach (
                        $teachersData
                        as $index => $item
                    ) {

                        if (! is_array($item)) {
                            $stats['failed']++;

                            $stats['errors'][] =
                                "Item index {$index} bukan format data yang valid.";

                            continue;
                        }

                        /*
                         * NIP
                         */

                        $nip = trim(
                            (string) (
                                $item['nip']
                                ?? $item['nis_nip']
                                ?? $item['nip_guru']
                                ?? $item['no_induk']
                                ?? $item['employee_id']
                                ?? $item['id_pegawai']
                                ?? ''
                            )
                        );

                        /*
                         * Nama
                         */

                        $nama = trim(
                            (string) (
                                $item['nama_lengkap']
                                ?? $item['name']
                                ?? $item['nama']
                                ?? ''
                            )
                        );

                        /*
                         * Guru tanpa NIP tetap diproses.
                         */

                        if (! $nip) {
                            $apiId =
                                $item['id']
                                ?? $item['guru_id']
                                ?? null;

                            $nip =
                                'HONOR-'
                                . (
                                    $apiId
                                    ?? ($index + 1)
                                );
                        }

                        $email = strtolower(
                            $nip
                            . '@smkn1bangsri.sch.id'
                        );

                        if (! $nama) {
                            $stats['failed']++;

                            $stats['errors'][] =
                                'Baris '
                                . ($index + 1)
                                . ': Nama kosong.';

                            continue;
                        }

                        try {

                            /*
                             * MATA PELAJARAN
                             */

                            $rawMapel =
                                $item['mata_pelajaran']
                                ?? $item['mapel']
                                ?? null;

                            if (is_array($rawMapel)) {
                                $mapel =
                                    implode(
                                        ', ',
                                        array_filter(
                                            array_map(
                                                'trim',
                                                $rawMapel
                                            )
                                        )
                                    );
                            } else {
                                $mapel =
                                    filled($rawMapel)
                                        ? trim(
                                            (string) $rawMapel
                                        )
                                        : null;
                            }

                            /*
                             * NOMOR HP
                             */

                            $hp =
                                data_get(
                                    $item,
                                    'no_telepon'
                                )
                                ?? data_get(
                                    $item,
                                    'hp'
                                )
                                ?? data_get(
                                    $item,
                                    'telepon'
                                )
                                ?? data_get(
                                    $item,
                                    'phone'
                                )
                                ?? data_get(
                                    $item,
                                    'whatsapp'
                                )
                                ?? data_get(
                                    $item,
                                    'mobile'
                                )
                                ?? null;

                            if ($hp) {
                                $hp =
                                    preg_replace(
                                        '/[^0-9+]/',
                                        '',
                                        trim(
                                            (string) $hp
                                        )
                                    );
                            }

                            /*
                             * ALAMAT
                             */

                            $alamat =
                                data_get(
                                    $item,
                                    'alamat'
                                )
                                ?? data_get(
                                    $item,
                                    'address'
                                )
                                ?? data_get(
                                    $item,
                                    'alamat_lengkap'
                                )
                                ?? null;

                            /*
                             * TANGGAL LAHIR
                             */

                            $tanggalLahir =
                                $item['tanggal_lahir']
                                ?? $item['tgl_lahir']
                                ?? null;

                            /*
                             * STATUS GURU
                             */

                            $statusAktif = true;

                            if (
                                array_key_exists(
                                    'status_aktif',
                                    $item
                                )
                            ) {
                                $statusAktif =
                                    $this->toBoolean(
                                        $item['status_aktif']
                                    )
                                    ?? true;

                            } elseif (
                                array_key_exists(
                                    'is_active',
                                    $item
                                )
                            ) {
                                $statusAktif =
                                    $this->toBoolean(
                                        $item['is_active']
                                    )
                                    ?? true;
                            }

                            /*
                             * PASSWORD
                             */

                            $passwordHash =
                                $this->resolvePasswordHash(
                                    $item,
                                    'password'
                                );

                            /*
                             * USER
                             */

                            $user =
                                User::where(
                                    'nis_nip',
                                    $nip
                                )
                                ->orWhere(
                                    'email',
                                    $email
                                )
                                ->first();

                            if ($user) {

                                $user->update([
                                    'name' =>
                                        $nama,

                                    'email' =>
                                        $email,

                                    'nis_nip' =>
                                        $nip,

                                    'password' =>
                                        $passwordHash,

                                    'role' =>
                                        'guru',
                                ]);

                                $stats['updated']++;

                            } else {

                                $user =
                                    User::create([
                                        'name' =>
                                            $nama,

                                        'email' =>
                                            $email,

                                        'password' =>
                                            $passwordHash,

                                        'role' =>
                                            'guru',

                                        'nis_nip' =>
                                            $nip,
                                    ]);

                                $stats['inserted']++;
                            }

                            /*
                             * GURU
                             */

                            $guru =
                                Guru::firstOrNew([
                                    'user_id' =>
                                        $user->id,
                                ]);

                            $guruData = [
                                'nip' =>
                                    $nip,

                                'nama_lengkap' =>
                                    $nama,

                                'tanggal_lahir' =>
                                    $tanggalLahir,

                                'status_aktif' =>
                                    $statusAktif,
                            ];

                            if (filled($mapel)) {
                                $guruData[
                                    'mata_pelajaran'
                                ] = $mapel;

                            } elseif (! $guru->exists) {
                                $guruData[
                                    'mata_pelajaran'
                                ] = 'Umum';
                            }

                            if (filled($hp)) {
                                $guruData[
                                    'no_telepon'
                                ] = $hp;
                            }

                            if (filled($alamat)) {
                                $guruData[
                                    'alamat'
                                ] = $alamat;
                            }

                            $guru->fill(
                                $guruData
                            );

                            $guru->save();

                        } catch (\Throwable $e) {

                            $stats['failed']++;

                            $stats['errors'][] =
                                "NIP {$nip} ({$nama}): "
                                . $e->getMessage();

                            Log::error(
                                "Err sync Guru NIP {$nip}: "
                                . $e->getMessage()
                            );
                        }
                    }

                    /*
                     * Audit log
                     */

                    if (auth()->check()) {
                        $this->auditLog->log(
                            auth()->id(),
                            'sync_sipintu_guru',
                            'guru',
                            null
                        );
                    }
                }
            );

            $summaryMsg =
                "Sinkronisasi Guru Selesai: "
                . "Total {$stats['total']} data "
                . "({$stats['inserted']} baru, "
                . "{$stats['updated']} diperbarui, "
                . "{$stats['failed']} gagal).";

            Log::info(
                $summaryMsg,
                $stats
            );

            return [
                'success' => true,
                'message' => $summaryMsg,
                'stats' => $stats,
            ];

        } catch (\Throwable $e) {

            Log::error(
                'SiPintu Sync Guru Exception: '
                . $e->getMessage()
            );

            return [
                'success' => false,

                'message' =>
                    'Terjadi kesalahan saat memproses '
                    . 'sinkronisasi SiPintu: '
                    . $e->getMessage(),

                'stats' => [
                    'total' => 0,
                    'inserted' => 0,
                    'updated' => 0,
                    'failed' => 0,
                    'skipped' => 0,
                    'errors' => [
                        $e->getMessage(),
                    ],
                ],
            ];
        }
    }
}
