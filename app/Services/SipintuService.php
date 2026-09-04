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
     * ==========================================================
     * RESOLVE PASSWORD
     * ==========================================================
     *
     * Password hanya dibuat ketika user BARU.
     *
     * User yang sudah ada tidak akan di-hash ulang setiap sync.
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
     * ==========================================================
     * BOOLEAN NORMALIZER
     * ==========================================================
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
            'masih aktif',
            'siswa aktif',
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
            'non aktif',
            'non-aktif',
            'inactive',
            'alumni',
            'lulus',
            'keluar',
            'berhenti',
            'dikeluarkan',
            'drop out',
            'pindah',
            'mutasi',
        ], true)) {
            return false;
        }

        return null;
    }

    /**
     * ==========================================================
     * EXTRACT NIS
     * ==========================================================
     */
    private function extractNis(array $item): string
    {
        return trim((string) (
            $item['nis']
            ?? $item['nis_nip']
            ?? $item['nisn']
            ?? data_get($item, 'student.nis')
            ?? data_get($item, 'siswa.nis')
            ?? data_get($item, 'data.nis')
            ?? ''
        ));
    }

    /**
     * ==========================================================
     * EXTRACT NAMA
     * ==========================================================
     */
    private function extractNama(array $item): string
    {
        return trim((string) (
            $item['nama_lengkap']
            ?? $item['name']
            ?? $item['nama']
            ?? data_get($item, 'student.nama')
            ?? data_get($item, 'student.nama_lengkap')
            ?? data_get($item, 'siswa.nama')
            ?? data_get($item, 'data.nama')
            ?? ''
        ));
    }

    /**
     * ==========================================================
     * EXTRACT NAMA KELAS
     * ==========================================================
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
            ?? data_get($item, 'student.rombel')
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
     * NORMALIZE KEY
     * ==========================================================
     */
    private function normalizeKey(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    /**
     * ==========================================================
     * CEK SISWA AKTIF
     * ==========================================================
     *
     * SECURITY RULE:
     *
     * Data hanya boleh masuk jika:
     *
     * 1. NIS dan nama tersedia.
     * 2. Tidak ada flag alumni.
     * 3. Tidak ada status nonaktif/alumni/lulus/keluar.
     * 4. Kelas valid X/XI/XII.
     * 5. Ada bukti eksplisit bahwa siswa aktif.
     *
     * Jika status aktif tidak dapat dikonfirmasi:
     * -> DATA DITOLAK.
     */
    private function isStudentActive(
        array $item,
        ?string &$reason = null
    ): bool {
        $reason = 'Status aktif tidak dapat dikonfirmasi';

        /*
         * ======================================================
         * 1. IDENTITAS
         * ======================================================
         */
        $nis = $this->extractNis($item);
        $nama = $this->extractNama($item);

        if ($nis === '') {
            $reason = 'NIS kosong';
            return false;
        }

        if ($nama === '') {
            $reason = 'Nama kosong';
            return false;
        }

        /*
         * ======================================================
         * 2. FLAG ALUMNI
         * ======================================================
         */
        $alumniFields = [
            'alumni',
            'is_alumni',
            'status_alumni',
            'isAlumni',
            'is_lulus',
            'lulus',

            'student.alumni',
            'student.is_alumni',
            'student.status_alumni',
            'student.is_lulus',

            'siswa.alumni',
            'siswa.is_alumni',
            'siswa.status_alumni',
            'siswa.is_lulus',

            'peserta_didik.alumni',
            'peserta_didik.is_alumni',

            'data.alumni',
            'data.is_alumni',
        ];

        foreach ($alumniFields as $field) {
            $value = data_get($item, $field);

            if ($value === null) {
                continue;
            }

            $boolean = $this->toBoolean($value);

            if ($boolean === true) {
                $reason = "Field {$field} menunjukkan ALUMNI";
                return false;
            }
        }

        /*
         * ======================================================
         * 3. STATUS TEKS
         * ======================================================
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

            'student.status',
            'student.student_status',
            'student.status_pendidikan',
            'student.status_keaktifan',

            'siswa.status',
            'siswa.status_siswa',
            'siswa.status_pendidikan',

            'peserta_didik.status',
            'peserta_didik.status_pendidikan',

            'data.status',
            'data.student_status',
            'data.status_pendidikan',
        ];

        $nonActiveKeywords = [
            'alumni',
            'alumnus',
            'lulus',
            'lulusan',
            'tamat',
            'tidak aktif',
            'nonaktif',
            'non aktif',
            'non-aktif',
            'inactive',
            'keluar',
            'berhenti',
            'dikeluarkan',
            'drop out',
            'dropout',
            'pindah',
            'mutasi',
            'meninggal',
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
        $hasUnknownStatus = false;
        $unknownStatus = '';

        foreach ($statusFields as $field) {
            $value = data_get($item, $field);

            if ($value === null || is_array($value)) {
                continue;
            }

            $text = trim((string) $value);

            if ($text === '') {
                continue;
            }

            $lower = strtolower($text);

            /*
             * Status angka / boolean.
             */
            $boolean = $this->toBoolean($value);

            if ($boolean === false) {
                $reason = "Status {$field} = {$text}";
                return false;
            }

            if ($boolean === true) {
                $hasExplicitActiveText = true;
                continue;
            }

            /*
             * Cek status nonaktif lebih dahulu.
             */
            foreach ($nonActiveKeywords as $badWord) {
                if (
                    $lower === $badWord
                    || str_contains($lower, $badWord)
                ) {
                    $reason = "Status {$field}: {$text}";
                    return false;
                }
            }

            /*
             * Cek status aktif.
             */
            foreach ($activeKeywords as $activeWord) {
                if (
                    $lower === $activeWord
                    || str_contains($lower, $activeWord)
                ) {
                    $hasExplicitActiveText = true;
                    break;
                }
            }

            if (! $hasExplicitActiveText) {
                $hasUnknownStatus = true;
                $unknownStatus = $text;
            }
        }

        /*
         * Jika ada status tidak dikenal dan tidak ada bukti aktif,
         * jangan masukkan data.
         */
        if ($hasUnknownStatus && ! $hasExplicitActiveText) {
            $reason = "Status tidak dikenal: {$unknownStatus}";
            return false;
        }

        /*
         * ======================================================
         * 4. VALIDASI KELAS
         * ======================================================
         */
        $namaKelas = $this->extractNamaKelas($item);

        if ($namaKelas === '') {
            $reason = 'Kelas kosong';
            return false;
        }

        $kelasLower = strtolower($namaKelas);

        foreach ([
            'alumni',
            'lulus',
            'lulusan',
            'keluar',
            'nonaktif',
            'non aktif',
            'non-aktif',
            'drop out',
            'dropout',
            'pindah',
            'mutasi',
        ] as $badWord) {
            if (str_contains($kelasLower, $badWord)) {
                $reason = "Kelas menunjukkan data tidak aktif: {$namaKelas}";
                return false;
            }
        }

        /*
         * Kelas harus X / XI / XII.
         */
        if (! preg_match(
            '/^(XII|XI|X)(?:\s+|-|_|\.|\/|$)/i',
            $namaKelas
        )) {
            $reason = "Tingkat kelas tidak valid: {$namaKelas}";
            return false;
        }

        /*
         * ======================================================
         * 5. STATUS AKTIF BOOLEAN
         * ======================================================
         */
        $activeFields = [
            'status_aktif',
            'is_active',
            'aktif',
            'active',
            'status_active',
            'aktif_status',

            'student.status_aktif',
            'student.is_active',
            'student.aktif',
            'student.active',

            'siswa.status_aktif',
            'siswa.is_active',
            'siswa.aktif',
            'siswa.active',

            'peserta_didik.status_aktif',
            'peserta_didik.is_active',

            'data.status_aktif',
            'data.is_active',
        ];

        $hasExplicitActiveBool = false;

        foreach ($activeFields as $field) {
            $value = data_get($item, $field);

            if ($value === null) {
                continue;
            }

            $boolean = $this->toBoolean($value);

            if ($boolean === false) {
                $reason = "Field {$field} = false";
                return false;
            }

            if ($boolean === true) {
                $hasExplicitActiveBool = true;
            }
        }

        /*
         * ======================================================
         * 6. FINAL SECURITY CHECK
         * ======================================================
         *
         * JANGAN gunakan $namaKelas sebagai alasan siswa aktif.
         *
         * Alumni bisa saja masih memiliki kelas X/XI/XII di API
         * tetapi status sebenarnya sudah tidak aktif.
         */
        if (
            $hasExplicitActiveBool
            || $hasExplicitActiveText
        ) {
            $reason = 'Aktif';
            return true;
        }

        $reason = 'Tidak ada bukti eksplisit bahwa siswa aktif';

        return false;
    }

    /**
     * ==========================================================
     * EKSTRAK JURUSAN
     * ==========================================================
     */
    private function extractJurusan(array $item, string $namaKelas): array
    {
        $kelasData = $item['kelas']
            ?? $item['classroom']
            ?? $item['nama_kelas']
            ?? $item['rombel']
            ?? data_get($item, 'student.kelas')
            ?? '';

        $jurusanData = $item['jurusan']
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

        $kode = '';
        $nama = '';

        if (is_array($jurusanData)) {
            $kode = trim((string) (
                $jurusanData['kode']
                ?? $jurusanData['kode_jurusan']
                ?? $jurusanData['code']
                ?? ''
            ));

            $nama = trim((string) (
                $jurusanData['nama']
                ?? $jurusanData['nama_jurusan']
                ?? $jurusanData['name']
                ?? ''
            ));
        } else {
            $nama = trim((string) $jurusanData);

            $kode = trim((string) (
                $item['kode_jurusan']
                ?? ''
            ));
        }

        /*
         * Jika jurusan tidak tersedia, ambil dari kelas.
         *
         * Contoh:
         * X PPLG 1
         * XI MPLB 2
         * XII PM 1
         */
        if (
            $kode === ''
            && $nama === ''
            && preg_match(
                '/^(?:XII|XI|X)\s+([A-Z0-9]+)/i',
                $namaKelas,
                $match
            )
        ) {
            $kode = strtoupper($match[1]);

            $nama = [
                'PPLG' => 'Pengembangan Perangkat Lunak dan Gim',
                'MPLB' => 'Manajemen Perkantoran dan Layanan Bisnis',
                'PM'   => 'Pemasaran',
                'AKL'  => 'Akuntansi Keuangan Lembaga',
                'TO'   => 'Teknik Otomotif',
            ][$kode] ?? $kode;
        }

        return [
            'kode' => $kode,
            'nama' => $nama,
        ];
    }

    /**
     * ==========================================================
     * NOMOR TELEPON
     * ==========================================================
     */
    private function extractPhone(array $item): ?string
    {
        $phone = data_get($item, 'no_telepon')
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

        if (! filled($phone)) {
            return null;
        }

        $phone = preg_replace(
            '/[^0-9+]/',
            '',
            trim((string) $phone)
        );

        return $phone !== '' ? $phone : null;
    }

    /**
     * ==========================================================
     * ALAMAT
     * ==========================================================
     */
    private function extractAddress(array $item): ?string
    {
        $address = data_get($item, 'alamat')
            ?? data_get($item, 'address')
            ?? data_get($item, 'alamat_lengkap')
            ?? data_get($item, 'full_address')
            ?? data_get($item, 'domisili');

        return filled($address)
            ? trim((string) $address)
            : null;
    }

    /**
     * ==========================================================
     * NONAKTIFKAN / BERSIHKAN SISWA YANG SUDAH TIDAK AKTIF
     * ==========================================================
     *
     * Jika siswa lama tidak lagi muncul sebagai aktif:
     *
     * - Jika belum memiliki dispensasi -> hapus siswa + user.
     * - Jika sudah memiliki dispensasi -> jangan hapus histori,
     *   cukup status_aktif = false.
     */
    private function cleanupInactiveStudents(array $nisList): void
    {
        if (empty($nisList)) {
            return;
        }

        $existingStudents = Siswa::withCount('dispensasi')
            ->with('user')
            ->whereIn('nis_nip', $nisList)
            ->get();

        foreach ($existingStudents as $siswa) {
            try {
                if ((int) $siswa->dispensasi_count === 0) {
                    $user = $siswa->user;

                    $siswa->delete();

                    if ($user) {
                        $user->delete();
                    }
                } else {
                    $siswa->update([
                        'status_aktif' => false,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning(
                    "Gagal cleanup siswa NIS {$siswa->nis_nip}: "
                    . $e->getMessage()
                );
            }
        }
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
            Log::info('MEMULAI SINKRONISASI SISWA AKTIF');
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
                        'Koneksi SiPintu berhasil, namun tidak ada data siswa.',
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
             * FILTER DATA API
             * ======================================================
             */
            $jumlahDariApi = count($studentsData);

            $activeStudents = [];
            $skippedNis = [];
            $jumlahDilewati = 0;

            foreach ($studentsData as $item) {
                if (! is_array($item)) {
                    $jumlahDilewati++;
                    continue;
                }

                $nis = $this->extractNis($item);
                $nama = $this->extractNama($item);

                $reason = '';

                if ($this->isStudentActive($item, $reason)) {
                    $activeStudents[] = $item;
                    continue;
                }

                $jumlahDilewati++;

                if ($nis !== '') {
                    $skippedNis[] = $nis;
                }

                /*
                 * Jangan masukkan data alumni/nonaktif.
                 */
                Log::info(
                    "SKIP SISWA: NIS {$nis} ({$nama}) - {$reason}"
                );
            }

            /*
             * Hapus / nonaktifkan data lokal yang sekarang sudah
             * terdeteksi bukan siswa aktif.
             */
            $skippedNis = array_values(
                array_unique(
                    array_filter($skippedNis)
                )
            );

            $this->cleanupInactiveStudents($skippedNis);

            $totalStudents = count($activeStudents);

            Log::info(
                "Total data dari API: {$jumlahDariApi}"
            );

            Log::info(
                "Total SISWA AKTIF: {$totalStudents}"
            );

            Log::info(
                "Total dilewati: {$jumlahDilewati}"
            );

            /*
             * ======================================================
             * SAFETY STOP
             * ======================================================
             *
             * Jika API mengirim data tetapi tidak ada satu pun
             * yang lolos filter, jangan menghapus massal seluruh
             * database siswa.
             */
            if ($totalStudents === 0) {
                return [
                    'success' => true,
                    'message' =>
                        "API mengirim {$jumlahDariApi} data, "
                        . "tetapi tidak ada data yang dapat "
                        . "dikonfirmasi sebagai siswa aktif. "
                        . "Tidak dilakukan sinkronisasi siswa baru.",
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
             * CACHE JURUSAN
             * ======================================================
             *
             * Hanya ambil sekali.
             */
            $jurusanMap = [];

            Jurusan::query()
                ->get([
                    'id',
                    'kode_jurusan',
                    'nama_jurusan',
                ])
                ->each(function (Jurusan $jurusan) use (&$jurusanMap) {
                    $kode = $this->normalizeKey(
                        $jurusan->kode_jurusan
                    );

                    $nama = $this->normalizeKey(
                        $jurusan->nama_jurusan
                    );

                    if ($kode !== '') {
                        $jurusanMap['kode:' . $kode] = $jurusan;
                    }

                    if ($nama !== '') {
                        $jurusanMap['nama:' . $nama] = $jurusan;
                    }
                });

            /*
             * ======================================================
             * BATCH PROCESS
             * ======================================================
             */
            $batchSize = 100;

            $batches = array_chunk(
                $activeStudents,
                $batchSize
            );

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
                        &$stats,
                        &$jurusanMap
                    ) {

                        /*
                         * ==================================================
                         * PRELOAD USER BATCH
                         * ==================================================
                         */
                        $nisList = [];
                        $emailList = [];

                        foreach ($batch as $item) {
                            if (! is_array($item)) {
                                continue;
                            }

                            $nis = $this->extractNis($item);

                            if ($nis === '') {
                                continue;
                            }

                            $nisList[] = $nis;
                            $emailList[] = strtolower(
                                $nis . '@smkn1bangsri.sch.id'
                            );
                        }

                        $nisList = array_values(
                            array_unique($nisList)
                        );

                        $emailList = array_values(
                            array_unique($emailList)
                        );

                        $users = User::query()
                            ->where(function ($query) use (
                                $nisList,
                                $emailList
                            ) {
                                $query
                                    ->whereIn(
                                        'nis_nip',
                                        $nisList
                                    )
                                    ->orWhereIn(
                                        'email',
                                        $emailList
                                    );
                            })
                            ->get();

                        $userMap = [];

                        foreach ($users as $user) {
                            if (filled($user->nis_nip)) {
                                $userMap[
                                    'nis:' . $this->normalizeKey(
                                        $user->nis_nip
                                    )
                                ] = $user;
                            }

                            if (filled($user->email)) {
                                $userMap[
                                    'email:' . $this->normalizeKey(
                                        $user->email
                                    )
                                ] = $user;
                            }
                        }

                        /*
                         * ==================================================
                         * PRELOAD SISWA
                         * ==================================================
                         */
                        $userIds = $users
                            ->pluck('id')
                            ->filter()
                            ->values()
                            ->all();

                        $siswaMap = [];

                        if (! empty($userIds)) {
                            Siswa::query()
                                ->whereIn('user_id', $userIds)
                                ->get()
                                ->each(
                                    function (Siswa $siswa) use (
                                        &$siswaMap
                                    ) {
                                        $siswaMap[
                                            (string) $siswa->user_id
                                        ] = $siswa;
                                    }
                                );
                        }

                        /*
                         * ==================================================
                         * PROSES SISWA
                         * ==================================================
                         */
                        foreach ($batch as $index => $item) {

                            if (! is_array($item)) {
                                $stats['failed']++;

                                $stats['errors'][] =
                                    "Item index {$index} bukan format valid.";

                                continue;
                            }

                            $nis = $this->extractNis($item);
                            $nama = $this->extractNama($item);

                            if (
                                $nis === ''
                                || $nama === ''
                            ) {
                                $stats['failed']++;

                                $stats['errors'][] =
                                    "Baris "
                                    . ($index + 1)
                                    . ": NIS atau Nama kosong.";

                                continue;
                            }

                            try {

                                /*
                                 * ==================================================
                                 * DATA DASAR
                                 * ==================================================
                                 */
                                $email = strtolower(
                                    $nis
                                    . '@smkn1bangsri.sch.id'
                                );

                                $namaKelas =
                                    $this->extractNamaKelas($item);

                                $jurusanData =
                                    $this->extractJurusan(
                                        $item,
                                        $namaKelas
                                    );

                                $kodeJurusan =
                                    trim(
                                        (string) (
                                            $jurusanData['kode']
                                            ?? ''
                                        )
                                    );

                                $namaJurusan =
                                    trim(
                                        (string) (
                                            $jurusanData['nama']
                                            ?? ''
                                        )
                                    );

                                /*
                                 * ==================================================
                                 * JURUSAN
                                 * ==================================================
                                 */
                                $jurusanObj = null;

                                if ($kodeJurusan !== '') {
                                    $jurusanObj =
                                        $jurusanMap[
                                            'kode:'
                                            . $this->normalizeKey(
                                                $kodeJurusan
                                            )
                                        ] ?? null;
                                }

                                if (
                                    ! $jurusanObj
                                    && $namaJurusan !== ''
                                ) {
                                    $jurusanObj =
                                        $jurusanMap[
                                            'nama:'
                                            . $this->normalizeKey(
                                                $namaJurusan
                                            )
                                        ] ?? null;
                                }

                                if (
                                    ! $jurusanObj
                                    && (
                                        $kodeJurusan !== ''
                                        || $namaJurusan !== ''
                                    )
                                ) {
                                    $kodeFinal =
                                        strtoupper(
                                            $kodeJurusan
                                            ?: substr(
                                                preg_replace(
                                                    '/[^A-Za-z0-9]/',
                                                    '',
                                                    $namaJurusan
                                                ),
                                                0,
                                                5
                                            )
                                        );

                                    $kodeFinal =
                                        $kodeFinal ?: 'UMUM';

                                    $namaFinal =
                                        $namaJurusan
                                        ?: $kodeFinal;

                                    $jurusanObj =
                                        Jurusan::create([
                                            'kode_jurusan' =>
                                                $kodeFinal,

                                            'nama_jurusan' =>
                                                $namaFinal,
                                        ]);

                                    /*
                                     * Masukkan ke cache agar siswa
                                     * berikutnya tidak query lagi.
                                     */
                                    $jurusanMap[
                                        'kode:'
                                        . $this->normalizeKey(
                                            $kodeFinal
                                        )
                                    ] = $jurusanObj;

                                    $jurusanMap[
                                        'nama:'
                                        . $this->normalizeKey(
                                            $namaFinal
                                        )
                                    ] = $jurusanObj;
                                }

                                /*
                                 * ==================================================
                                 * KELAS
                                 * ==================================================
                                 */
                                $kelasObj = null;

                                if (
                                    $namaKelas !== ''
                                    && $jurusanObj
                                ) {

                                    $kelasKey =
                                        $this->normalizeKey(
                                            $namaKelas
                                        );

                                    /*
                                     * Query hanya untuk kelas yang
                                     * dibutuhkan. Setelah ditemukan,
                                     * langsung digunakan kembali.
                                     */
                                    $kelasObj =
                                        Kelas::query()
                                            ->where(
                                                'jurusan_id',
                                                $jurusanObj->id
                                            )
                                            ->whereRaw(
                                                'LOWER(TRIM(nama_kelas)) = ?',
                                                [$kelasKey]
                                            )
                                            ->first();

                                    /*
                                     * Jika belum ada dengan jurusan
                                     * tersebut, cari kelas global.
                                     */
                                    if (! $kelasObj) {
                                        $kelasObj =
                                            Kelas::query()
                                                ->whereRaw(
                                                    'LOWER(TRIM(nama_kelas)) = ?',
                                                    [$kelasKey]
                                                )
                                                ->first();

                                        if ($kelasObj) {
                                            $kelasObj->update([
                                                'jurusan_id' =>
                                                    $jurusanObj->id,
                                            ]);
                                        }
                                    }

                                    /*
                                     * Buat kelas baru.
                                     */
                                    if (! $kelasObj) {
                                        $tingkat = 'X';

                                        if (
                                            preg_match(
                                                '/^(XII|XI|X)\b/i',
                                                $namaKelas,
                                                $match
                                            )
                                        ) {
                                            $tingkat =
                                                strtoupper(
                                                    $match[1]
                                                );
                                        }

                                        $kelasObj =
                                            Kelas::create([
                                                'nama_kelas' =>
                                                    $namaKelas,

                                                'jurusan_id' =>
                                                    $jurusanObj->id,

                                                'tingkat' =>
                                                    $tingkat,
                                            ]);
                                    }
                                }

                                $jurusanId =
                                    $kelasObj?->jurusan_id
                                    ?? $jurusanObj?->id;

                                /*
                                 * ==================================================
                                 * DATA SISWA
                                 * ==================================================
                                 */
                                $phone =
                                    $this->extractPhone($item);

                                $alamat =
                                    $this->extractAddress($item);

                                $tanggalLahir =
                                    $item['tanggal_lahir']
                                    ?? $item['tgl_lahir']
                                    ?? data_get(
                                        $item,
                                        'student.tanggal_lahir'
                                    )
                                    ?? null;

                                /*
                                 * ==================================================
                                 * USER
                                 * ==================================================
                                 */
                                $user =
                                    $userMap[
                                        'nis:'
                                        . $this->normalizeKey($nis)
                                    ]
                                    ?? $userMap[
                                        'email:'
                                        . $this->normalizeKey($email)
                                    ]
                                    ?? null;

                                $isNewUser = false;

                                if ($user) {

                                    /*
                                     * ==================================================
                                     * USER LAMA
                                     * ==================================================
                                     *
                                     * PENTING:
                                     * Password TIDAK disentuh.
                                     *
                                     * Ini menghindari:
                                     * Hash::make() x 1.300 setiap sync.
                                     */
                                    $user->update([
                                        'name' =>
                                            $nama,

                                        'email' =>
                                            $email,

                                        'nis_nip' =>
                                            $nis,

                                        'role' =>
                                            'siswa',
                                    ]);

                                    $stats['updated']++;

                                } else {

                                    /*
                                     * ==================================================
                                     * USER BARU
                                     * ==================================================
                                     */
                                    $passwordHash =
                                        $this->resolvePasswordHash(
                                            $item,
                                            'password'
                                        );

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

                                    $isNewUser = true;

                                    $stats['inserted']++;
                                }

                                /*
                                 * Simpan ke cache user.
                                 */
                                $userMap[
                                    'nis:'
                                    . $this->normalizeKey($nis)
                                ] = $user;

                                $userMap[
                                    'email:'
                                    . $this->normalizeKey($email)
                                ] = $user;

                                /*
                                 * ==================================================
                                 * SISWA
                                 * ==================================================
                                 */
                                $siswa =
                                    $siswaMap[
                                        (string) $user->id
                                    ]
                                    ?? null;

                                if (! $siswa) {
                                    $siswa =
                                        new Siswa();

                                    $siswa->user_id =
                                        $user->id;

                                    $siswaMap[
                                        (string) $user->id
                                    ] = $siswa;
                                }

                                $siswa->nis_nip =
                                    $nis;

                                $siswa->jurusan_id =
                                    $jurusanId;

                                $siswa->kelas_id =
                                    $kelasObj?->id;

                                $siswa->nama_lengkap =
                                    $nama;

                                /*
                                 * Karena sudah lolos filter aktif.
                                 */
                                $siswa->status_aktif =
                                    true;

                                if (filled($tanggalLahir)) {
                                    $siswa->tanggal_lahir =
                                        $tanggalLahir;
                                }

                                if (filled($alamat)) {
                                    $siswa->alamat =
                                        $alamat;
                                }

                                if (filled($phone)) {
                                    $siswa->no_telepon =
                                        $phone;
                                }

                                $siswa->save();

                            } catch (\Throwable $e) {

                                $stats['failed']++;

                                $stats['errors'][] =
                                    "NIS {$nis} ({$nama}): "
                                    . $e->getMessage();

                                Log::error(
                                    "ERROR SYNC SISWA NIS {$nis}: "
                                    . $e->getMessage()
                                );
                            }
                        }
                    }
                );

                /*
                 * Jangan terlalu agresif terhadap API/database.
                 *
                 * 100 data -> jeda 0.2 detik.
                 */
                if (
                    $batchIndex
                    < $totalBatches - 1
                ) {
                    usleep(200000);
                }

                gc_collect_cycles();
            }

            /*
             * ==========================================================
             * AUDIT LOG
             * ==========================================================
             */
            if (auth()->check()) {
                try {
                    $this->auditLog->log(
                        auth()->id(),
                        'sync_sipintu_siswa',
                        'siswa',
                        null
                    );
                } catch (\Throwable $e) {
                    Log::warning(
                        'Gagal membuat audit log sync siswa: '
                        . $e->getMessage()
                    );
                }
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
            Log::info('MEMULAI SINKRONISASI GURU');
            Log::info('==========================================');

            $apiResult =
                $this->apiService->getGuruData();

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

            $teachersData =
                $apiResult['data'] ?? [];

            if (
                ! is_array($teachersData)
                || empty($teachersData)
            ) {
                return [
                    'success' => true,
                    'message' =>
                        'Koneksi SiPintu berhasil, '
                        . 'namun tidak ada data guru.',
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

            $totalGuru =
                count($teachersData);

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
                                "Item index {$index} "
                                . "bukan format valid.";

                            continue;
                        }

                        /*
                         * NIP
                         */
                        $nip = trim((string) (
                            $item['nip']
                            ?? $item['nis_nip']
                            ?? $item['nip_guru']
                            ?? $item['no_induk']
                            ?? $item['employee_id']
                            ?? $item['id_pegawai']
                            ?? ''
                        ));

                        /*
                         * Nama
                         */
                        $nama = trim((string) (
                            $item['nama_lengkap']
                            ?? $item['name']
                            ?? $item['nama']
                            ?? ''
                        ));

                        /*
                         * Guru tanpa NIP tetap diproses.
                         */
                        if ($nip === '') {
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

                        if ($nama === '') {
                            $stats['failed']++;

                            $stats['errors'][] =
                                'Baris '
                                . ($index + 1)
                                . ': Nama kosong.';

                            continue;
                        }

                        $email = strtolower(
                            $nip
                            . '@smkn1bangsri.sch.id'
                        );

                        try {

                            /*
                             * ==================================================
                             * MAPEL
                             * ==================================================
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
                             * ==================================================
                             * HP
                             * ==================================================
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
                                );

                            if (filled($hp)) {
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
                                );

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
                             * STATUS GURU
                             * ==================================================
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
                             * ==================================================
                             * USER
                             * ==================================================
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

                                /*
                                 * Password user lama TIDAK disentuh.
                                 */
                                $user->update([
                                    'name' =>
                                        $nama,

                                    'email' =>
                                        $email,

                                    'nis_nip' =>
                                        $nip,

                                    'role' =>
                                        'guru',
                                ]);

                                $stats['updated']++;

                            } else {

                                /*
                                 * Password hanya di-hash
                                 * untuk user baru.
                                 */
                                $passwordHash =
                                    $this->resolvePasswordHash(
                                        $item,
                                        'password'
                                    );

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
                             * ==================================================
                             * GURU
                             * ==================================================
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
                                "ERROR SYNC GURU NIP {$nip}: "
                                . $e->getMessage()
                            );
                        }
                    }

                    /*
                     * Audit log
                     */
                    if (auth()->check()) {
                        try {
                            $this->auditLog->log(
                                auth()->id(),
                                'sync_sipintu_guru',
                                'guru',
                                null
                            );
                        } catch (\Throwable $e) {
                            Log::warning(
                                'Gagal membuat audit log sync guru: '
                                . $e->getMessage()
                            );
                        }
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
