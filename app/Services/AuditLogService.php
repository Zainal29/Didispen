<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Throwable;

class AuditLogService
{
    /**
     * Simpan aktivitas ke audit log.
     *
     * Audit log tidak boleh membuat proses utama aplikasi gagal.
     */
    public function log(
        int $userId,
        string $action,
        string $tableName,
        ?int $recordId = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): ?AuditLog {
        try {
            $request = request();

            return AuditLog::create([
                'user_id'     => $userId,
                'action'      => $action,
                'table_name'  => $tableName,
                'record_id'   => $recordId ?? 0,
                'old_value'   => $oldValue,
                'new_value'   => $newValue,

                'ip_address'  => $request->ip(),
                'device_type' => $this->detectDeviceType($request),
                'os'          => $this->detectOperatingSystem($request),
                'browser'     => $this->detectBrowser($request),
                'user_agent'  => $request->userAgent(),
            ]);
        } catch (Throwable $e) {
            /*
             * Audit log jangan sampai membuat aplikasi utama error.
             */
            report($e);

            return null;
        }
    }

    /**
     * Deteksi jenis perangkat.
     */
    private function detectDeviceType(Request $request): string
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        if ($userAgent === '') {
            return 'Unknown';
        }

        /*
         * Tablet harus dicek sebelum mobile.
         */
        if (
            str_contains($userAgent, 'ipad') ||
            str_contains($userAgent, 'tablet') ||
            (
                str_contains($userAgent, 'android') &&
                !str_contains($userAgent, 'mobile')
            )
        ) {
            return 'Tablet';
        }

        /*
         * Smartphone.
         */
        if (
            str_contains($userAgent, 'mobile') ||
            str_contains($userAgent, 'android') ||
            str_contains($userAgent, 'iphone') ||
            str_contains($userAgent, 'ipod')
        ) {
            return 'HP';
        }

        /*
         * Selain mobile/tablet dianggap komputer.
         */
        if (
            str_contains($userAgent, 'windows') ||
            str_contains($userAgent, 'macintosh') ||
            str_contains($userAgent, 'linux') ||
            str_contains($userAgent, 'x11') ||
            str_contains($userAgent, 'cros')
        ) {
            return 'Laptop/PC';
        }

        return 'Unknown';
    }

    /**
     * Deteksi sistem operasi.
     */
    private function detectOperatingSystem(Request $request): string
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        if ($userAgent === '') {
            return 'Unknown';
        }

        /*
         * iPhone / iPad
         */
        if (
            str_contains($userAgent, 'iphone') ||
            str_contains($userAgent, 'ipod')
        ) {
            return 'iOS';
        }

        if (str_contains($userAgent, 'ipad')) {
            return 'iPadOS';
        }

        /*
         * Android
         */
        if (str_contains($userAgent, 'android')) {
            if (preg_match('/android\s+([\d\.]+)/i', $userAgent, $matches)) {
                return 'Android ' . $matches[1];
            }

            return 'Android';
        }

        /*
         * Windows
         */
        if (str_contains($userAgent, 'windows nt 10.0')) {
            return 'Windows 10/11';
        }

        if (str_contains($userAgent, 'windows nt 6.3')) {
            return 'Windows 8.1';
        }

        if (str_contains($userAgent, 'windows nt 6.2')) {
            return 'Windows 8';
        }

        if (str_contains($userAgent, 'windows nt 6.1')) {
            return 'Windows 7';
        }

        /*
         * macOS
         */
        if (str_contains($userAgent, 'mac os x')) {
            if (preg_match('/mac os x ([\d_]+)/i', $userAgent, $matches)) {
                return 'macOS ' . str_replace('_', '.', $matches[1]);
            }

            return 'macOS';
        }

        /*
         * ChromeOS
         */
        if (str_contains($userAgent, 'cros')) {
            return 'ChromeOS';
        }

        /*
         * Linux
         */
        if (str_contains($userAgent, 'linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }

    /**
     * Deteksi browser.
     */
    private function detectBrowser(Request $request): string
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        if ($userAgent === '') {
            return 'Unknown';
        }

        /*
         * Edge harus diperiksa sebelum Chrome.
         */
        if (
            str_contains($userAgent, 'edg/') ||
            str_contains($userAgent, 'edge/')
        ) {
            if (preg_match('/edg(?:e|a|ios)?\/([\d\.]+)/i', $userAgent, $matches)) {
                return 'Microsoft Edge ' . $matches[1];
            }

            return 'Microsoft Edge';
        }

        /*
         * Opera.
         */
        if (
            str_contains($userAgent, 'opr/') ||
            str_contains($userAgent, 'opera')
        ) {
            if (preg_match('/opr\/([\d\.]+)/i', $userAgent, $matches)) {
                return 'Opera ' . $matches[1];
            }

            return 'Opera';
        }

        /*
         * Firefox.
         */
        if (str_contains($userAgent, 'firefox/')) {
            if (preg_match('/firefox\/([\d\.]+)/i', $userAgent, $matches)) {
                return 'Firefox ' . $matches[1];
            }

            return 'Firefox';
        }

        /*
         * Samsung Internet.
         */
        if (str_contains($userAgent, 'samsungbrowser/')) {
            if (preg_match('/samsungbrowser\/([\d\.]+)/i', $userAgent, $matches)) {
                return 'Samsung Internet ' . $matches[1];
            }

            return 'Samsung Internet';
        }

        /*
         * Chrome.
         */
        if (
            str_contains($userAgent, 'chrome/') ||
            str_contains($userAgent, 'crios/')
        ) {
            if (preg_match('/(?:chrome|crios)\/([\d\.]+)/i', $userAgent, $matches)) {
                return 'Chrome ' . $matches[1];
            }

            return 'Chrome';
        }

        /*
         * Safari.
         */
        if (
            str_contains($userAgent, 'safari/') &&
            !str_contains($userAgent, 'chrome') &&
            !str_contains($userAgent, 'crios')
        ) {
            return 'Safari';
        }

        return 'Unknown';
    }
}
