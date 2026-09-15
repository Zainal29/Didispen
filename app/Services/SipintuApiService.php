<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SipintuApiService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.sipintu.url', 'http://localhost:8000'),
            '/'
        );

        $this->clientId = config(
            'services.sipintu.client_id',
            'app_muzl3or17cqw'
        );

        $this->clientSecret = (string) config(
            'services.sipintu.client_secret',
            'secret'
        );

        $this->cacheTtl = (int) config(
            'services.sipintu.cache_ttl',
            300
        );
    }

    /**
     * Request helper dengan Authentication Headers & Retry Logic
     */
    private function request(
        string $endpoint,
        array $queryParams = [],
        int $timeout = 30
    ) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        return Http::retry(2, 500)
            ->withHeaders([
                'X-Client-ID'     => $this->clientId,
                'X-Client-Secret' => $this->clientSecret,
                'Accept'          => 'application/json',
            ])
            ->timeout($timeout)
            ->get($url, $queryParams);
    }

    /**
     * Ambil data Siswa dari API SiPintu
     *
     * Tidak menggunakan force.
     * Data API disimpan di cache sesuai cache_ttl.
     */
    public function getSiswaData(): array
    {
        $cacheKey = 'sipintu_siswa_data';

        if (Cache::has($cacheKey)) {
            Log::info(
                'SipintuApiService: Mengambil data siswa dari cache.'
            );

            return [
                'status'     => 'success',
                'from_cache' => true,
                'data'       => Cache::get($cacheKey),
            ];
        }

        try {
            Log::info(
                'SipintuApiService: Mengontak API SiPintu untuk data siswa...'
            );

            $response = $this->request(
                '/api/v1/sijuna/students',
                [],
                60
            );

            if (! $response->successful()) {
                Log::error(
                    'SipintuApiService: Gagal mengambil data siswa. Status: '
                    . $response->status()
                );

                if (Cache::has($cacheKey)) {
                    return [
                        'status'     => 'fallback',
                        'from_cache' => true,
                        'message'    => 'API SiPintu tidak merespon (Status: '
                            . $response->status()
                            . '). Menggunakan data cache terakhir.',
                        'data'       => Cache::get($cacheKey),
                    ];
                }

                return [
                    'status'     => 'error',
                    'from_cache' => false,
                    'message'    => 'Gagal terhubung ke API SiPintu (Status: '
                        . $response->status()
                        . ').',
                    'data'       => [],
                ];
            }

            $raw = $response->json();

            $data = $raw['data']
                ?? $raw['students']
                ?? (is_array($raw) ? $raw : []);

            if (is_array($data) && ! empty($data)) {
                Cache::put(
                    $cacheKey,
                    $data,
                    $this->cacheTtl
                );
            }

            Log::info(
                'SipintuApiService: Data siswa dari API = '
                . (is_array($data) ? count($data) : 0)
            );

            return [
                'status'     => 'success',
                'from_cache' => false,
                'data'       => $data,
            ];

        } catch (\Throwable $e) {
            Log::error(
                'SipintuApiService getSiswaData Exception: '
                . $e->getMessage()
            );

            if (Cache::has($cacheKey)) {
                return [
                    'status'     => 'fallback',
                    'from_cache' => true,
                    'message'    => 'Terjadi kesalahan koneksi API SiPintu: '
                        . $e->getMessage()
                        . '. Menggunakan data cache terakhir.',
                    'data'       => Cache::get($cacheKey),
                ];
            }

            return [
                'status'     => 'error',
                'from_cache' => false,
                'message'    => 'Koneksi ke API SiPintu gagal: '
                    . $e->getMessage(),
                'data'       => [],
            ];
        }
    }

    /**
     * Ambil data Guru dari API SiPintu
     *
     * Semua guru yang dikirim endpoint diproses.
     * Jika endpoint SiPintu mengirim 72 guru, maka 72 guru akan diproses.
     */
    public function getGuruData(): array
    {
        $cacheKey = 'sipintu_guru_data';

        if (Cache::has($cacheKey)) {
            Log::info(
                'SipintuApiService: Mengambil data guru dari cache.'
            );

            return [
                'status'     => 'success',
                'from_cache' => true,
                'data'       => Cache::get($cacheKey),
            ];
        }

        try {
            Log::info(
                'SipintuApiService: Mengontak API SiPintu untuk data guru...'
            );

            $response = $this->request(
                '/api/v1/sijuna/teachers',
                [],
                45
            );

            if (! $response->successful()) {
                Log::error(
                    'SipintuApiService: Gagal mengambil data guru. Status: '
                    . $response->status()
                );

                if (Cache::has($cacheKey)) {
                    return [
                        'status'     => 'fallback',
                        'from_cache' => true,
                        'message'    => 'API SiPintu tidak merespon (Status: '
                            . $response->status()
                            . '). Menggunakan data cache terakhir.',
                        'data'       => Cache::get($cacheKey),
                    ];
                }

                return [
                    'status'     => 'error',
                    'from_cache' => false,
                    'message'    => 'Gagal terhubung ke API SiPintu (Status: '
                        . $response->status()
                        . ').',
                    'data'       => [],
                ];
            }

            $raw = $response->json();

            $data = $raw['data']
                ?? $raw['teachers']
                ?? (is_array($raw) ? $raw : []);

            if (is_array($data) && ! empty($data)) {
                Cache::put(
                    $cacheKey,
                    $data,
                    $this->cacheTtl
                );
            }

            Log::info(
                'SipintuApiService: Data guru dari API = '
                . (is_array($data) ? count($data) : 0)
            );

            return [
                'status'     => 'success',
                'from_cache' => false,
                'data'       => $data,
            ];

        } catch (\Throwable $e) {
            Log::error(
                'SipintuApiService getGuruData Exception: '
                . $e->getMessage()
            );

            if (Cache::has($cacheKey)) {
                return [
                    'status'     => 'fallback',
                    'from_cache' => true,
                    'message'    => 'Terjadi kesalahan koneksi API SiPintu: '
                        . $e->getMessage()
                        . '. Menggunakan data cache terakhir.',
                    'data'       => Cache::get($cacheKey),
                ];
            }

            return [
                'status'     => 'error',
                'from_cache' => false,
                'message'    => 'Koneksi ke API SiPintu gagal: '
                    . $e->getMessage(),
                'data'       => [],
            ];
        }
    }

    /**
     * Ambil data Kelas dari API SiPintu
     */
    public function getKelasData(): array
    {
        $cacheKey = 'sipintu_kelas_data';

        if (Cache::has($cacheKey)) {
            return [
                'status'     => 'success',
                'from_cache' => true,
                'data'       => Cache::get($cacheKey),
            ];
        }

        try {
            $response = $this->request(
                '/api/v1/sijuna/classrooms',
                [],
                30
            );

            if ($response->successful()) {
                $raw = $response->json();

                $data = $raw['data']
                    ?? $raw['classrooms']
                    ?? $raw['classes']
                    ?? (is_array($raw) ? $raw : []);

                Cache::put(
                    $cacheKey,
                    $data,
                    $this->cacheTtl
                );

                return [
                    'status'     => 'success',
                    'from_cache' => false,
                    'data'       => $data,
                ];
            }

            return [
                'status'  => 'error',
                'message' => 'Gagal mengambil data kelas',
                'data'    => [],
            ];

        } catch (\Throwable $e) {
            Log::error(
                'SipintuApiService getKelasData Exception: '
                . $e->getMessage()
            );

            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    /**
     * Ambil data Jurusan dari API SiPintu
     */
    public function getJurusanData(): array
    {
        $cacheKey = 'sipintu_jurusan_data';

        if (Cache::has($cacheKey)) {
            return [
                'status'     => 'success',
                'from_cache' => true,
                'data'       => Cache::get($cacheKey),
            ];
        }

        try {
            $response = $this->request(
                '/api/v1/sijuna/majors',
                [],
                30
            );

            if ($response->successful()) {
                $raw = $response->json();

                $data = $raw['data']
                    ?? $raw['majors']
                    ?? $raw['jurusan']
                    ?? (is_array($raw) ? $raw : []);

                Cache::put(
                    $cacheKey,
                    $data,
                    $this->cacheTtl
                );

                return [
                    'status'     => 'success',
                    'from_cache' => false,
                    'data'       => $data,
                ];
            }

            return [
                'status'  => 'error',
                'message' => 'Gagal mengambil data jurusan',
                'data'    => [],
            ];

        } catch (\Throwable $e) {
            Log::error(
                'SipintuApiService getJurusanData Exception: '
                . $e->getMessage()
            );

            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }
    }
}
