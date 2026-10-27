<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBaileysWhatsAppJob;
use App\Models\JadwalPiket;
use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Services\WhatsappMessageService;
use App\Services\WhatsAppBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappBotController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsappLog::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('context')) {
            $query->where('context', $request->context);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(15)->withQueryString();

        // Statistik ringkas
        $stats = [
            'total'   => WhatsappLog::count(),
            'sent'    => WhatsappLog::where('status', 'sent')->count(),
            'pending' => WhatsappLog::where('status', 'pending')->count(),
            'failed'  => WhatsappLog::where('status', 'failed')->count(),
        ];

        $rawActiveDays = Setting::get('wa_active_days');
        $activeDays = $rawActiveDays !== null ? (json_decode($rawActiveDays, true) ?? []) : [1, 2, 3, 4, 5];

        // Konfigurasi saat ini dari Database (fallback ke config/services.php)
        $settings = [
            'wa_bot_enabled'               => filter_var(Setting::get('wa_bot_enabled', config('services.whatsapp_bot.enabled', true)), FILTER_VALIDATE_BOOLEAN),
            'wa_bot_url'                   => Setting::get('wa_bot_url', config('services.whatsapp_bot.url', 'http://127.0.0.1:3789')),
            'wa_bot_api_key'               => Setting::get('wa_bot_api_key', config('services.whatsapp_bot.api_key', 'sipintu_wa_secret_key_2026')),
            'wa_notify_piket_bertugas'     => filter_var(Setting::get('wa_notify_piket_bertugas', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_notify_koordinator_piket'  => filter_var(Setting::get('wa_notify_koordinator_piket', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_notify_piket_swap'         => filter_var(Setting::get('wa_notify_piket_swap', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_notify_piket_swap_result'  => filter_var(Setting::get('wa_notify_piket_swap_result', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_piket_reminder_minutes'    => (int) Setting::get('wa_piket_reminder_minutes', 15),
            'wa_notify_siswa_terlambat'    => filter_var(Setting::get('wa_notify_siswa_terlambat', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_notify_laporan_guru_piket' => filter_var(Setting::get('wa_notify_laporan_guru_piket', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_satpam_phone'              => Setting::get('wa_satpam_phone', ''),
            'wa_notify_satpam_sesi_piket'  => filter_var(Setting::get('wa_notify_satpam_sesi_piket', '1'), FILTER_VALIDATE_BOOLEAN),
            'wa_active_days'               => $activeDays,
        ];

        // Cek status koneksi & QR code ke microservice Baileys
        $botStatus = $this->fetchBotStatus();

        // Matriks Agenda Pengiriman Bot Harian (Senin s.d. Jumat/Minggu)
        $matriksData = JadwalPiket::with([
            'koordinator',
            'guru' => function ($q) {
                $q->orderBy('nama_lengkap');
            }
        ])
        ->where('is_active', true)
        ->orderBy('hari')
        ->orderBy('jam_mulai')
        ->get()
        ->groupBy('hari');

        return view('admin.whatsapp-bot.index', compact('logs', 'stats', 'botStatus', 'settings', 'matriksData'));
    }

    /**
     * Endpoint AJAX Polling Status & QR Code (Real-time di browser)
     */
    public function statusAjax()
    {
        $botStatus = $this->fetchBotStatus();
        return response()->json($botStatus);
    }

    /**
     * Saklar Cepat (Quick Toggle) Master Bot ON / OFF
     */
    public function toggle()
    {
        $current = filter_var(Setting::get('wa_bot_enabled', config('services.whatsapp_bot.enabled', true)), FILTER_VALIDATE_BOOLEAN);
        $newStatus = ! $current;
        Setting::set('wa_bot_enabled', $newStatus ? '1' : '0');

        $statusText = $newStatus ? 'diaktifkan (ON)' : 'dinonaktifkan (OFF)';
        return back()->with('success', "Master WhatsApp Bot berhasil {$statusText}.");
    }

    /**
     * Simpan Pengaturan Bot WhatsApp ke Database Setting
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'wa_bot_url'                => 'nullable|url',
            'wa_bot_api_key'            => 'nullable|string|max:255',
            'wa_satpam_phone'           => 'nullable|string|max:30',
            'wa_piket_reminder_minutes' => 'nullable|integer|min:0|max:120',
        ], [
            'wa_bot_url.url'                    => 'Format URL Server Bot tidak valid.',
            'wa_piket_reminder_minutes.integer' => 'Waktu pengingat harus berupa angka menit.',
            'wa_piket_reminder_minutes.min'     => 'Waktu pengingat minimal 0 menit.',
            'wa_piket_reminder_minutes.max'     => 'Waktu pengingat maksimal 120 menit.',
        ]);

        if (!empty($validated['wa_bot_url'])) {
            Setting::set('wa_bot_url', rtrim($validated['wa_bot_url'], '/'));
        }
        if (!empty($validated['wa_bot_api_key'])) {
            Setting::set('wa_bot_api_key', $validated['wa_bot_api_key']);
        }
        Setting::set('wa_bot_enabled', $request->boolean('wa_bot_enabled') ? '1' : '0');
        Setting::set('wa_satpam_phone', preg_replace('/[^0-9]/', '', (string) $request->input('wa_satpam_phone', '')));
        Setting::set('wa_piket_reminder_minutes', (string) ($request->input('wa_piket_reminder_minutes', 15)));
        Setting::set('wa_notify_piket_bertugas', $request->boolean('wa_notify_piket_bertugas') ? '1' : '0');
        Setting::set('wa_notify_koordinator_piket', $request->boolean('wa_notify_koordinator_piket') ? '1' : '0');
        Setting::set('wa_notify_piket_swap', $request->boolean('wa_notify_piket_swap') ? '1' : '0');
        Setting::set('wa_notify_piket_swap_result', $request->boolean('wa_notify_piket_swap_result') ? '1' : '0');
        Setting::set('wa_notify_siswa_terlambat', $request->boolean('wa_notify_siswa_terlambat') ? '1' : '0');
        Setting::set('wa_notify_laporan_guru_piket', $request->boolean('wa_notify_laporan_guru_piket') ? '1' : '0');
        Setting::set('wa_notify_satpam_sesi_piket', $request->boolean('wa_notify_satpam_sesi_piket') ? '1' : '0');

        // Simpan wa_active_days bahkan jika semua hari dicopot centangnya ([])
        $activeDays = array_map('intval', (array) $request->input('wa_active_days', []));
        Setting::set('wa_active_days', json_encode(array_values($activeDays)));

        return back()->with('success', 'Pengaturan WhatsApp Bot berhasil diperbarui.');
    }

    /**
     * Putuskan Sesi WhatsApp (Logout & hapus auth folder) untuk scan nomor baru
     */
    public function disconnect()
    {
        $clientId = config('services.sipintu.client_id');
        if (!empty($clientId)) {
            $sipintuUrl = rtrim((string) (config('services.sipintu.url') ?: 'https://sipintu.smkn1bangsri.sch.id'), '/');
            return back()->with('info', "Pengelolaan nomor WhatsApp terpusat di SiPintu Gateway. Silakan buka menu WhatsApp Gateway di SiPintu ({$sipintuUrl}/admin/whatsapp-gateway) untuk menautkan atau mengganti nomor.");
        }

        $botUrl = rtrim(Setting::get('wa_bot_url', config('services.whatsapp_bot.url', 'http://127.0.0.1:3789')), '/');
        $apiKey = Setting::get('wa_bot_api_key', config('services.whatsapp_bot.api_key', 'sipintu_wa_secret_key_2026'));

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(5)
                ->post("{$botUrl}/logout");

            if ($response->successful()) {
                return back()->with('success', 'Sesi WhatsApp berhasil diputuskan. Silakan scan QR Code baru di bawah ini.');
            }

            return back()->with('error', 'Gagal logout: ' . $response->body());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal terhubung ke Bot Baileys: ' . $e->getMessage());
        }
    }

    /**
     * Restart / Sinkronisasi Socket Baileys / SiPintu Gateway
     */
    public function restart()
    {
        $clientId = config('services.sipintu.client_id');
        $clientSecret = config('services.sipintu.client_secret');

        if (!empty($clientId) && !empty($clientSecret)) {
            $status = WhatsAppBot::status();
            $connected = $status['data']['whatsapp_gateway']['is_connected'] ?? false;
            $phone = $status['data']['whatsapp_gateway']['assigned_phone'] ?? null;
            if ($connected) {
                return back()->with('success', "Koneksi ke SiPintu Gateway aktif dan siap mengirim (Nomor: +{$phone}).");
            }
            return back()->with('success', 'Status koneksi SiPintu Gateway berhasil diperbarui.');
        }

        $botUrl = rtrim(Setting::get('wa_bot_url', config('services.whatsapp_bot.url', 'http://127.0.0.1:3789')), '/');
        $apiKey = Setting::get('wa_bot_api_key', config('services.whatsapp_bot.api_key', 'sipintu_wa_secret_key_2026'));

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(5)
                ->post("{$botUrl}/restart");

            if ($response->successful()) {
                return back()->with('success', 'Socket Baileys sedang direstart. Mohon tunggu beberapa detik.');
            }

            return back()->with('error', 'Gagal restart: ' . $response->body());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal terhubung ke Bot Baileys: ' . $e->getMessage());
        }
    }

    /**
     * Kirim ulang (Retry) satu pesan yang berstatus failed / pending
     */
    public function retry(WhatsappLog $log)
    {
        if (empty($log->phone)) {
            return back()->with('error', 'Nomor telepon kosong, tidak dapat mengirim ulang.');
        }

        $log->update([
            'status'        => 'pending',
            'error_message' => null,
        ]);

        SendBaileysWhatsAppJob::dispatch($log->id);

        return back()->with('success', "Pesan untuk {$log->recipient_name} ({$log->phone}) berhasil dimasukkan kembali ke antrean.");
    }

    /**
     * Kirim ulang semua pesan yang gagal (Bulk Retry)
     */
    public function retryAllFailed()
    {
        $failedLogs = WhatsappLog::where('status', 'failed')
            ->whereNotNull('phone')
            ->get();

        foreach ($failedLogs as $log) {
            $log->update(['status' => 'pending', 'error_message' => null]);
            SendBaileysWhatsAppJob::dispatch($log->id);
        }

        return back()->with('success', "{$failedLogs->count()} pesan gagal berhasil dijadwalkan ulang ke antrean.");
    }

    /**
     * Fitur Tes Kirim Pesan dari Dashboard Admin
     */
    public function sendTest(Request $request, WhatsappMessageService $waService)
    {
        $request->validate([
            'phone'   => 'required|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        $log = $waService->dispatchBaileysMessage(
            rawPhone: $request->phone,
            message: $request->message,
            context: 'test_admin',
            userId: auth()->id(),
            recipientName: 'Tes Admin (' . (auth()->user()->name ?? 'Admin') . ')',
            referenceKey: null // tanpa kunci deduplikasi agar bisa dites berkali-kali
        );

        if (! $log || $log->status === 'failed') {
            return back()->with('error', 'Nomor tujuan tidak valid atau fitur WA Bot sedang dinonaktifkan.');
        }

        return back()->with('success', 'Pesan uji coba berhasil dikirim ke antrean Queue Worker.');
    }

    /**
     * Ambil status detail dan koneksi nomor WhatsApp
     */
    private function fetchBotStatus(): array
    {
        $clientId = config('services.sipintu.client_id');
        $clientSecret = config('services.sipintu.client_secret');

        // Prioritas 1: SiPintu Multi-Session WhatsApp Gateway
        if (!empty($clientId) && !empty($clientSecret)) {
            $res = WhatsAppBot::status();
            if ($res['success']) {
                $data = $res['data'] ?? [];

                // Format resmi SiPintu Gateway
                if (isset($data['whatsapp_gateway'])) {
                    $gw = $data['whatsapp_gateway'];
                    $app = $data['application'] ?? [];
                    $isConnected = (bool) ($gw['is_connected'] ?? false);
                    $isFallback = (bool) ($gw['auto_fallback_to_central'] ?? false);
                    $assignedPhone = $gw['assigned_phone'] ?? null;

                    return [
                        'online'         => true,
                        'connected'      => $isConnected,
                        'gateway_type'   => 'sipintu',
                        'status'         => $isConnected ? 'connected' : 'waiting_qr',
                        'assigned_phone' => $assignedPhone,
                        'auto_fallback'  => $isFallback,
                        'user'           => [
                            'phone' => $assignedPhone ?: ($isFallback ? 'Nomor Pusat SiPintu (Auto-Fallback Aktif)' : 'Belum Ditautkan'),
                            'name'  => ($app['name'] ?? 'DiDispen') . ' (SiPintu Gateway)',
                        ],
                        'qr'             => null,
                        'qr_image'       => null,
                        'message'        => $isConnected
                            ? 'Terhubung ke WhatsApp Gateway SiPintu (' . $assignedPhone . ')'
                            : 'Nomor khusus belum ditautkan di SiPintu' . ($isFallback ? ' (Pesan dialihkan via Nomor Pusat)' : ''),
                    ];
                }

                // Format mock unit test / respon generik
                return [
                    'online'    => (bool) ($data['online'] ?? true),
                    'connected' => (bool) ($data['connected'] ?? false),
                    'status'    => $data['status'] ?? (($data['connected'] ?? false) ? 'connected' : 'disconnected'),
                    'user'      => $data['user'] ?? null,
                    'qr'        => $data['qr'] ?? null,
                    'qr_image'  => $data['qr_image'] ?? null,
                    'message'   => $data['message'] ?? 'Bot Gateway Aktif',
                ];
            }

            return [
                'online'        => false,
                'connected'     => false,
                'gateway_type'  => 'sipintu',
                'status'        => 'offline',
                'user'          => null,
                'qr'            => null,
                'qr_image'      => null,
                'message'       => 'Tidak dapat terhubung ke SiPintu Gateway: ' . ($res['error'] ?? ''),
            ];
        }

        // Prioritas 2 (Fallback): Microservice Baileys lokal
        $botUrl = rtrim(Setting::get('wa_bot_url', config('services.whatsapp_bot.url', 'http://127.0.0.1:3789')), '/');
        $apiKey = Setting::get('wa_bot_api_key', config('services.whatsapp_bot.api_key', 'sipintu_wa_secret_key_2026'));

        try {
            $response = Http::withHeaders(['x-api-key' => $apiKey])
                ->timeout(3)
                ->get("{$botUrl}/status");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'online'    => true,
                    'connected' => $data['connected'] ?? false,
                    'status'    => $data['status'] ?? ($data['connected'] ? 'connected' : 'disconnected'),
                    'user'      => $data['user'] ?? null,
                    'qr'        => $data['qr'] ?? null,
                    'qr_image'  => $data['qr_image'] ?? null,
                    'message'   => $data['message'] ?? 'Bot Baileys Aktif',
                ];
            }

            return [
                'online'    => true,
                'connected' => false,
                'status'    => 'error',
                'user'      => null,
                'qr'        => null,
                'qr_image'  => null,
                'message'   => 'Server Bot merespon dengan HTTP ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'online'    => false,
                'connected' => false,
                'status'    => 'offline',
                'user'      => null,
                'qr'        => null,
                'qr_image'  => null,
                'message'   => 'Tidak dapat terhubung ke Bot Baileys di ' . $botUrl,
            ];
        }
    }
}