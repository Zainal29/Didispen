<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    public function __construct(
        private ?AuditLogService $auditLog = null
    ) {
        $this->auditLog = $auditLog ?? app(AuditLogService::class);
    }

    /**
     * =========================================================================
     * JALUR 1: CALLBACK SSO SIPINTU GATEWAY
     * =========================================================================
     * Menerima redirect dari Portal SiPintu Gateway setelah pengguna memilih
     * aplikasi DIDISPEN, menukarkan authorization code dengan access token,
     * mengambil data profil pengguna, menyinkronkan user ke database lokal,
     * dan langsung mengarahkan ke dashboard.
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');

        if (! $code) {
            return redirect()->route('login')->withErrors([
                'email' => 'Otorisasi SSO SiPintu gagal: Kode otorisasi tidak ditemukan.'
            ]);
        }

        $baseUrl = rtrim(
            config('services.sipintu.base_url')
                ?: config('services.sipintu.url')
                ?: env('SIPINTU_BASE_URL', 'http://localhost:8000'),
            '/'
        );

        $clientId = config('services.sipintu.client_id')
            ?: env('SIPINTU_CLIENT_ID');

        $clientSecret = config('services.sipintu.client_secret')
            ?: env('SIPINTU_CLIENT_SECRET');

        $redirectUri = config('services.sipintu.redirect_uri')
            ?: env('SIPINTU_REDIRECT_URI', route('oauth.callback'));

        // Step 1: Tukarkan Authorization Code dengan Access Token (Server-to-Server)
        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->timeout(15)
                ->post("{$baseUrl}/oauth/token", [
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri'  => $redirectUri,
                    'code'          => $code,
                ]);
        } catch (\Throwable $e) {
            Log::error('SiPintu SSO Token Exchange Exception: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal menghubungi server SiPintu Gateway saat menukarkan token.'
            ]);
        }

        if ($tokenResponse->failed()) {
            $errorDesc = $tokenResponse->json('error_description')
                ?? $tokenResponse->json('message')
                ?? 'Gagal memverifikasi token ke SiPintu Gateway.';

            Log::warning('SiPintu SSO Token Exchange Failed', [
                'status' => $tokenResponse->status(),
                'response' => $tokenResponse->body(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => $errorDesc
            ]);
        }

        $accessToken = $tokenResponse->json('access_token');

        if (! $accessToken) {
            return redirect()->route('login')->withErrors([
                'email' => 'Access token tidak ditemukan dalam respons SiPintu Gateway.'
            ]);
        }

        // Step 2: Ambil Data Profil Pengguna & Password Hash dari SiPintu Gateway
        try {
            $userResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->get("{$baseUrl}/api/v1/user");
        } catch (\Throwable $e) {
            Log::error('SiPintu SSO User Profile Exception: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email' => 'Gagal mengambil profil akun dari SiPintu Gateway.'
            ]);
        }

        if ($userResponse->failed()) {
            Log::warning('SiPintu SSO User Profile Failed', [
                'status' => $userResponse->status(),
                'response' => $userResponse->body(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal mengambil data akun pengguna dari SiPintu Gateway.'
            ]);
        }

        $sipintuUser = $userResponse->json('data') ?? $userResponse->json();

        // Normalisasi data pengguna
        $email = strtolower(trim((string) ($sipintuUser['email'] ?? '')));
        $externalId = trim((string) ($sipintuUser['external_id'] ?? $sipintuUser['id'] ?? ''));
        $nisNip = trim((string) (
            $sipintuUser['nis']
            ?? $sipintuUser['nip']
            ?? $sipintuUser['external_id']
            ?? $sipintuUser['nis_nip']
            ?? ''
        ));
        $name = trim((string) ($sipintuUser['name'] ?? 'User SiPintu'));

        // Pemetaan Peran (Role Mapping)
        $rawRole = strtolower(trim((string) ($sipintuUser['role'] ?? 'user')));
        $role = match ($rawRole) {
            'student', 'siswa' => 'siswa',
            'teacher', 'guru' => 'guru',
            'admin' => 'admin',
            'satpam', 'security' => 'satpam',
            default => 'siswa',
        };

        // Password hash dari SiPintu Gateway
        $incomingPassword = $sipintuUser['password']
            ?? $sipintuUser['password_hash']
            ?? null;

        // Step 3: Pencocokan dengan data lokal atau Auto-Provisioning
        $user = User::where(function ($query) use ($email, $externalId, $nisNip) {
            if ($externalId !== '') {
                $query->where('external_id', $externalId);
            }
            if ($nisNip !== '') {
                $query->orWhere('nis_nip', $nisNip);
            }
            if ($email !== '') {
                $query->orWhere('email', $email);
            }
        })->first();

        $syncTime = now();

        if (! $user) {
            // Auto-provision user baru jika belum terdaftar
            $user = User::create([
                'name'                   => $name,
                'email'                  => $email ?: ($externalId . '@smkn1bangsri.sch.id'),
                'role'                   => $role,
                'external_id'            => $externalId ?: null,
                'nis_nip'                => $nisNip ?: null,
                'password'               => $incomingPassword ?: bcrypt(Str::random(32)),
                'email_verified_at'      => $syncTime,
                'sipintu_last_synced_at' => $syncTime,
            ]);
        } else {
            // User sudah ada: sinkronkan external_id, password hash, dan role jika perlu
            $updates = [
                'sipintu_last_synced_at' => $syncTime,
            ];

            if ($externalId !== '' && empty($user->external_id)) {
                $updates['external_id'] = $externalId;
            }

            if ($nisNip !== '' && empty($user->nis_nip)) {
                $updates['nis_nip'] = $nisNip;
            }

            if ($incomingPassword && $user->password !== $incomingPassword) {
                $updates['password'] = $incomingPassword;
            }

            // Jika user pernah dinonaktifkan / locked, pulihkan jika login SSO berhasil
            if ($user->locked_until || $user->failed_login_attempts > 0) {
                $updates['failed_login_attempts'] = 0;
                $updates['locked_until'] = null;
            }

            $user->update($updates);
        }

        // Step 4: Pastikan relasi model Siswa atau Guru tersedia
        if ($user->role === 'siswa') {
            Siswa::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nis_nip'      => $user->nis_nip,
                    'nama_lengkap' => $user->name,
                    'status_aktif' => true,
                ]
            );
        } elseif ($user->role === 'guru') {
            Guru::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'          => $user->nis_nip,
                    'nama_lengkap' => $user->name,
                    'status_aktif' => true,
                ]
            );
        }

        // Step 5: Autentikasikan sesi lokal
        Auth::login($user, true);
        $request->session()->regenerate();

        // Audit Log
        try {
            $this->auditLog?->log(
                $user->id,
                'login_sso_sipintu',
                'users',
                $user->id,
                null,
                [
                    'provider' => 'sipintu_gateway',
                    'role'     => $user->role,
                ]
            );
        } catch (\Throwable $e) {
            // Abaikan kesalahan audit log
        }

        // Arahkan ke dashboard sesuai role pengguna
        $dashboardUrl = match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'guru'   => route('guru.dashboard'),
            'satpam' => route('satpam.dashboard'),
            default  => route('siswa.dashboard'),
        };

        return redirect()->intended($dashboardUrl)->with(
            'success',
            "Selamat datang kembali, {$user->name}!"
        );
    }

    /**
     * =========================================================================
     * STEP 4: WEBHOOK SINKRONISASI REAL-TIME & SMART CONFLICT RESOLUTION
     * =========================================================================
     * Menerima pembaruan pengguna dari SiPintu Gateway via POST /api/sipintu/sync-user.
     * Menggunakan verifikasi HMAC SHA-256 dan deteksi editan lokal
     * agar perubahan profil lokal tidak terhapus.
     */
    public function syncUser(Request $request)
    {
        // 1. Verifikasi Signature HMAC SHA-256
        $secret = config('services.sipintu.client_secret')
            ?: env('SIPINTU_CLIENT_SECRET');

        if (! empty($secret)) {
            $signature = $request->header('X-SiPintu-Signature');
            $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);

            if (! $signature || ! hash_equals($computedSignature, $signature)) {
                Log::warning('SiPintu Webhook: Invalid signature', [
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid signature.',
                ], 401);
            }
        }

        $userData = $request->input('user') ?? $request->all();
        $previous = $request->input('previous', []);

        // Validasi kelengkapan data
        $email = strtolower(trim((string) ($userData['email'] ?? '')));
        if ($email === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid payload: Email is required.',
            ], 400);
        }

        $externalId = trim((string) ($userData['external_id'] ?? $userData['id'] ?? ''));
        $previousEmail = strtolower(trim((string) ($previous['email'] ?? '')));
        $nisNip = trim((string) (
            $userData['nis']
            ?? $userData['nip']
            ?? $userData['nis_nip']
            ?? ''
        ));

        // 2. Cari User di Database Lokal
        $user = User::where(function ($q) use ($email, $previousEmail, $externalId, $nisNip) {
            $q->where('email', $email);
            if ($previousEmail !== '') {
                $q->orWhere('email', $previousEmail);
            }
            if ($externalId !== '') {
                $q->orWhere('external_id', $externalId);
            }
            if ($nisNip !== '') {
                $q->orWhere('nis_nip', $nisNip);
            }
        })->first();

        // Pemetaan Peran
        $rawRole = strtolower(trim((string) ($userData['role'] ?? 'user')));
        $role = match ($rawRole) {
            'student', 'siswa' => 'siswa',
            'teacher', 'guru' => 'guru',
            'admin' => 'admin',
            'satpam', 'security' => 'satpam',
            default => 'siswa',
        };

        $syncTime = now();

        // 3. Jika belum ada: Auto-provision akun baru
        if (! $user) {
            $password = $userData['password']
                ?? $userData['password_hash']
                ?? bcrypt(Str::random(32));

            $user = User::create([
                'name'                   => $userData['name'] ?? 'User',
                'email'                  => $email,
                'role'                   => $role,
                'external_id'            => $externalId ?: null,
                'nis_nip'                => $nisNip ?: null,
                'password'               => $password,
                'email_verified_at'      => $syncTime,
                'sipintu_last_synced_at' => $syncTime,
            ]);

            // Buat relasi Siswa atau Guru
            if ($user->role === 'siswa') {
                Siswa::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nis_nip'      => $user->nis_nip,
                        'nama_lengkap' => $user->name,
                        'no_telepon'   => $userData['phone'] ?? null,
                        'status_aktif' => true,
                    ]
                );
            } elseif ($user->role === 'guru') {
                Guru::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip'          => $user->nis_nip,
                        'nama_lengkap' => $user->name,
                        'no_telepon'   => $userData['phone'] ?? null,
                        'status_aktif' => true,
                    ]
                );
            }

            Log::info("SiPintu Webhook: User Created [ID: {$user->id}, Email: {$user->email}]");

            return response()->json([
                'status'    => 'success',
                'action'    => 'created',
                'user_id'   => $user->id,
                'timestamp' => $syncTime->toIso8601String(),
            ], 201);
        }

        // 4. Deteksi Perubahan Lokal Pengguna (Smart Conflict Resolution)
        $hasLocalEdits = false;
        $skippedFields = [];

        if ($user->sipintu_last_synced_at !== null && $user->updated_at->gt($user->sipintu_last_synced_at)) {
            $hasLocalEdits = true;
        }

        // Field Selalu Mengikuti SiPintu (Source of Truth)
        $updateFields = [
            'email' => $email,
            'role'  => $role,
        ];

        if ($externalId !== '') {
            $updateFields['external_id'] = $externalId;
        }

        if (! empty($userData['password']) || ! empty($userData['password_hash'])) {
            $updateFields['password'] = $userData['password'] ?? $userData['password_hash'];
        }

        // Field Lokal: Hanya ditimpa jika TIDAK ADA perubahan lokal
        if ($hasLocalEdits) {
            $skippedFields = ['name'];
            if (isset($userData['phone'])) {
                $skippedFields[] = 'phone';
            }
            Log::info("SiPintu Webhook: Local edits detected for User [ID: {$user->id}], preserving local profile.");
        } else {
            if (! empty($userData['name'])) {
                $updateFields['name'] = $userData['name'];
            }
            if ($nisNip !== '') {
                $updateFields['nis_nip'] = $nisNip;
            }

            // Selaraskan nomor telepon ke Siswa/Guru jika dikirimkan
            if (! empty($userData['phone'])) {
                if ($user->role === 'siswa' && $user->siswa) {
                    $user->siswa->update(['no_telepon' => $userData['phone']]);
                } elseif ($user->role === 'guru' && $user->guru) {
                    $user->guru->update(['no_telepon' => $userData['phone']]);
                }
            }
        }

        // 5. Update & Selaraskan Timestamp (mencegah false positive di sync berikutnya)
        $updateFields['sipintu_last_synced_at'] = $syncTime;

        $user->fill($updateFields);
        $user->sipintu_last_synced_at = $syncTime;
        $user->updated_at = $syncTime;
        $user->save();

        Log::info("SiPintu Webhook: User Updated [ID: {$user->id}]", [
            'has_local_edits' => $hasLocalEdits,
            'skipped_fields'  => $skippedFields,
            'updated_fields'  => array_keys($updateFields),
        ]);

        return response()->json([
            'status'          => 'success',
            'action'          => 'updated',
            'user_id'         => $user->id,
            'has_local_edits' => $hasLocalEdits,
            'skipped_fields'  => $skippedFields,
            'timestamp'       => $syncTime->toIso8601String(),
        ], 200);
    }
}
