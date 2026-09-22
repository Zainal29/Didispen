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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class OAuthController extends Controller
{
    public function __construct(
        private ?AuditLogService $auditLog = null
    ) {
        $this->auditLog = $auditLog ?? app(AuditLogService::class);
    }

    /**
     * Mapping role SiPintu ke role lokal DIDISPEN.
     */
    private function mapRole(string $rawRole): ?string
    {
        return match (strtolower(trim($rawRole))) {
            'student', 'siswa' => 'siswa',
            'teacher', 'guru' => 'guru',
            'satpam', 'security' => 'satpam',
            default => null,
        };
    }

    /**
     * Verifikasi signature webhook HMAC SHA-256.
     */
    private function verifyWebhookSignature(Request $request): bool
    {
        $secret = config('services.sipintu.client_secret')
            ?: env('SIPINTU_CLIENT_SECRET');

        // Webhook wajib memiliki secret.
        if (! is_string($secret) || trim($secret) === '') {
            Log::critical('SiPintu Webhook: Secret belum dikonfigurasi.');
            return false;
        }

        $signature = trim(
            (string) $request->header('X-SiPintu-Signature', '')
        );

        // Mendukung format: sha256=signature atau signature biasa.
        if (str_starts_with($signature, 'sha256=')) {
            $signature = substr($signature, 7);
        }

        if ($signature === '') {
            return false;
        }

        $computedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            $secret
        );

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Pastikan relasi Siswa/Guru tersedia.
     */
    private function ensureUserProfile(User $user): void
    {
        if ($user->role === 'siswa') {
            Siswa::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nis_nip' => $user->nis_nip,
                    'nama_lengkap' => $user->name,
                    'status_aktif' => true,
                ]
            );
        } elseif ($user->role === 'guru') {
            Guru::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $user->nis_nip,
                    'nama_lengkap' => $user->name,
                    'status_aktif' => true,
                ]
            );
        }
    }

    /**
     * CALLBACK SSO SIPINTU
     */
    public function callback(Request $request)
    {
        $code = $request->input('code');

        if (! is_string($code) || trim($code) === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Kode otorisasi SSO tidak ditemukan.',
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

        if (! $clientId || ! $clientSecret) {
            Log::critical('SiPintu SSO: Konfigurasi client belum lengkap.');

            return redirect()->route('login')->withErrors([
                'email' => 'Konfigurasi SSO belum lengkap.',
            ]);
        }

        /*
         * STEP 1: Tukarkan authorization code dengan access token.
         */
        try {
            $tokenResponse = Http::asForm()
                ->acceptJson()
                ->timeout(15)
                ->post("{$baseUrl}/oauth/token", [
                    'grant_type' => 'authorization_code',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ]);
        } catch (\Throwable $e) {
            Log::error('SiPintu SSO Token Exchange Exception', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal menghubungi server SiPintu.',
            ]);
        }

        if ($tokenResponse->failed()) {
            Log::warning('SiPintu SSO Token Exchange Failed', [
                'status' => $tokenResponse->status(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal memverifikasi token SiPintu.',
            ]);
        }

        $accessToken = $tokenResponse->json('access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Access token tidak ditemukan.',
            ]);
        }

        /*
         * STEP 2: Ambil profil pengguna dari SiPintu.
         */
        try {
            $userResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->get("{$baseUrl}/api/v1/user");
        } catch (\Throwable $e) {
            Log::error('SiPintu SSO Profile Exception', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal mengambil profil akun SiPintu.',
            ]);
        }

        if ($userResponse->failed()) {
            Log::warning('SiPintu SSO Profile Failed', [
                'status' => $userResponse->status(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Gagal mengambil data akun SiPintu.',
            ]);
        }

        $sipintuUser = $userResponse->json('data')
            ?? $userResponse->json();

        if (! is_array($sipintuUser)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Format profil SiPintu tidak valid.',
            ]);
        }

        /*
         * STEP 3: Normalisasi dan validasi profil.
         */
        $email = strtolower(trim(
            (string) ($sipintuUser['email'] ?? '')
        ));

        $externalId = trim((string) (
            $sipintuUser['external_id']
            ?? $sipintuUser['id']
            ?? ''
        ));

        $nisNip = trim((string) (
            $sipintuUser['nis']
            ?? $sipintuUser['nip']
            ?? $sipintuUser['nis_nip']
            ?? ''
        ));

        $name = trim((string) (
            $sipintuUser['name'] ?? ''
        ));

        $rawRole = strtolower(trim((string) (
            $sipintuUser['role'] ?? ''
        )));

        $role = $this->mapRole($rawRole);

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Email dari SiPintu tidak valid.',
            ]);
        }

        // Jangan menerima role yang tidak dikenal.
        if ($role === null) {
            Log::warning('SiPintu SSO: Role tidak dikenal.', [
                'email' => $email,
                'role' => $rawRole,
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Role akun tidak diizinkan untuk login SSO.',
            ]);
        }

        /*
         * STEP 4: Pencocokan akun lokal.
         *
         * Prioritas:
         * 1. external_id
         * 2. email jika belum ditemukan
         *
         * Jangan mencari berdasarkan external_id kosong.
         */
        $user = null;

        if ($externalId !== '') {
            $user = User::where('external_id', $externalId)->first();
        }

        if (! $user) {
            $user = User::where('email', $email)->first();
        }

        /*
         * STEP 5: Tolak akun admin lokal.
         *
         * Pemeriksaan dilakukan terhadap role lokal.
         * Jadi role SSO siswa tidak bisa masuk ke akun admin lokal.
         */
        if ($user && $user->role === 'admin') {
            Log::warning('SiPintu SSO: Login admin lokal ditolak.', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Admin wajib login melalui form lokal DIDISPEN.',
            ]);
        }

        /*
         * Jangan mengizinkan role SSO berbeda dari role lokal.
         */
        if ($user && $user->role !== $role) {
            Log::warning('SiPintu SSO: Role mismatch.', [
                'user_id' => $user->id,
                'local_role' => $user->role,
                'sipintu_role' => $role,
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Role akun SiPintu tidak sesuai dengan akun DIDISPEN.',
            ]);
        }

        $syncTime = now();

        /*
         * STEP 6: Auto-provision akun non-admin.
         */
        if (! $user) {
            $user = User::create([
                'name' => $name !== '' ? $name : 'User SiPintu',
                'email' => $email,
                'role' => $role,
                'external_id' => $externalId !== ''
                    ? $externalId
                    : null,
                'nis_nip' => $nisNip !== ''
                    ? $nisNip
                    : null,

                // Password acak, tidak mengambil password SiPintu.
                'password' => Hash::make(Str::random(64)),

                'email_verified_at' => $syncTime,
                'sipintu_last_synced_at' => $syncTime,
            ]);
        } else {
            /*
             * STEP 7: Sinkronisasi data dasar.
             *
             * Tidak mengubah:
             * - password
             * - role
             * - failed_login_attempts
             * - locked_until
             */
            $updates = [
                'sipintu_last_synced_at' => $syncTime,
            ];

            if ($externalId !== '' && empty($user->external_id)) {
                $updates['external_id'] = $externalId;
            }

            if ($nisNip !== '' && empty($user->nis_nip)) {
                $updates['nis_nip'] = $nisNip;
            }

            if ($name !== '' && empty($user->name)) {
                $updates['name'] = $name;
            }

            $user->update($updates);
        }

        /*
         * STEP 8: Pastikan relasi profil tersedia.
         */
        $this->ensureUserProfile($user);

        /*
         * STEP 9: Login lokal.
         */
        Auth::login($user, true);

        $request->session()->regenerate();

        /*
         * STEP 10: Audit log.
         */
        try {
            $this->auditLog?->log(
                $user->id,
                'login_sso_sipintu',
                'users',
                $user->id,
                null,
                [
                    'provider' => 'sipintu_gateway',
                    'role' => $user->role,
                    'ip' => $request->ip(),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Audit log SSO gagal.', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
        }

        $dashboardUrl = match ($user->role) {
            'guru' => route('guru.dashboard'),
            'satpam' => route('satpam.dashboard'),
            default => route('siswa.dashboard'),
        };

        return redirect()
            ->intended($dashboardUrl)
            ->with('success', "Selamat datang, {$user->name}!");
    }

    /**
     * WEBHOOK SINKRONISASI REAL-TIME
     */
    public function syncUser(Request $request)
    {
        /*
         * STEP 1: Wajib verifikasi signature.
         */
        if (! $this->verifyWebhookSignature($request)) {
            Log::warning('SiPintu Webhook: Invalid signature.', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature.',
            ], 401);
        }

        /*
         * STEP 2: Validasi payload.
         */
        $userData = $request->input('user') ?? $request->all();
        $previous = $request->input('previous', []);

        if (! is_array($userData) || ! is_array($previous)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payload.',
            ], 400);
        }

        $validator = Validator::make($userData, [
            'email' => ['required', 'string', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:50'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'id' => ['nullable', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nis_nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payload.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($userData['email']));

        $externalId = trim((string) (
            $userData['external_id']
            ?? $userData['id']
            ?? ''
        ));

        $nisNip = trim((string) (
            $userData['nis']
            ?? $userData['nip']
            ?? $userData['nis_nip']
            ?? ''
        ));

        $rawRole = strtolower(trim($userData['role']));
        $role = $this->mapRole($rawRole);

        /*
         * STEP 3: Tolak role tidak dikenal/admin.
         */
        if ($role === null) {
            Log::warning('SiPintu Webhook: Role tidak diizinkan.', [
                'email' => $email,
                'role' => $rawRole,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Role not allowed.',
            ], 403);
        }

        /*
         * STEP 4: Cari akun lokal.
         */
        $user = null;

        if ($externalId !== '') {
            $user = User::where('external_id', $externalId)->first();
        }

        if (! $user) {
            $user = User::where('email', $email)->first();
        }

        /*
         * Jangan membuat atau mengubah akun admin.
         */
        if ($user && $user->role === 'admin') {
            Log::warning('SiPintu Webhook: Admin sync ditolak.', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Admin sync not allowed.',
            ], 403);
        }

        /*
         * STEP 5: Cegah role lokal berubah melalui webhook.
         */
        if ($user && $user->role !== $role) {
            Log::warning('SiPintu Webhook: Role mismatch.', [
                'user_id' => $user->id,
                'local_role' => $user->role,
                'incoming_role' => $role,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Role mismatch.',
            ], 409);
        }

        $syncTime = now();

        /*
         * STEP 6: Auto-provision akun baru.
         */
        if (! $user) {
            $name = trim((string) ($userData['name'] ?? ''));

            $user = User::create([
                'name' => $name !== '' ? $name : 'User SiPintu',
                'email' => $email,
                'role' => $role,
                'external_id' => $externalId !== ''
                    ? $externalId
                    : null,
                'nis_nip' => $nisNip !== ''
                    ? $nisNip
                    : null,

                // Tidak menerima password dari webhook.
                'password' => Hash::make(Str::random(64)),

                'email_verified_at' => $syncTime,
                'sipintu_last_synced_at' => $syncTime,
            ]);

            $this->ensureUserProfile($user);

            return response()->json([
                'status' => 'success',
                'action' => 'created',
                'user_id' => $user->id,
                'timestamp' => $syncTime->toIso8601String(),
            ], 201);
        }

        /*
         * STEP 7: Deteksi perubahan lokal.
         */
        $hasLocalEdits = false;
        $skippedFields = [];

        if (
            $user->sipintu_last_synced_at !== null
            && $user->updated_at !== null
            && $user->updated_at->gt($user->sipintu_last_synced_at)
        ) {
            $hasLocalEdits = true;
        }

        /*
         * Email dan external_id disinkronkan dari SiPintu.
         * Role dan password tidak pernah ditimpa.
         */
        $updateFields = [
            'email' => $email,
            'sipintu_last_synced_at' => $syncTime,
        ];

        if ($externalId !== '') {
            $updateFields['external_id'] = $externalId;
        }

        if ($hasLocalEdits) {
            $skippedFields[] = 'name';
            $skippedFields[] = 'nis_nip';
            $skippedFields[] = 'phone';

            Log::info('SiPintu Webhook: Perubahan lokal dipertahankan.', [
                'user_id' => $user->id,
            ]);
        } else {
            $name = trim((string) ($userData['name'] ?? ''));

            if ($name !== '') {
                $updateFields['name'] = $name;
            }

            if ($nisNip !== '') {
                $updateFields['nis_nip'] = $nisNip;
            }

            $phone = trim((string) ($userData['phone'] ?? ''));

            if ($phone !== '') {
                if ($user->role === 'siswa' && $user->siswa) {
                    $user->siswa->update([
                        'no_telepon' => $phone,
                    ]);
                } elseif ($user->role === 'guru' && $user->guru) {
                    $user->guru->update([
                        'no_telepon' => $phone,
                    ]);
                }
            }
        }

        /*
         * STEP 8: Simpan perubahan.
         *
         * Tidak menyentuh password, role, atau lockout.
         */
        $user->fill($updateFields);
        $user->save();

        Log::info('SiPintu Webhook: User updated.', [
            'user_id' => $user->id,
            'has_local_edits' => $hasLocalEdits,
            'skipped_fields' => $skippedFields,
            'updated_fields' => array_keys($updateFields),
        ]);

        return response()->json([
            'status' => 'success',
            'action' => 'updated',
            'user_id' => $user->id,
            'has_local_edits' => $hasLocalEdits,
            'skipped_fields' => $skippedFields,
            'timestamp' => $syncTime->toIso8601String(),
        ], 200);
    }
}
