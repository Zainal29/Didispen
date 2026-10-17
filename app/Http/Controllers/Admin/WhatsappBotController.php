<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBaileysWhatsAppJob;
use App\Models\JadwalPiket;
use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Services\WhatsappMessageService;
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

        // Konfigurasi saat ini dari Database (fallback ke config/services.php)
        $settings = [
            'wa_bot_enabled'               => (bool) Setting::get('wa_bot_enabled', config('services.whatsapp_bot.enabled', true)),
            'wa_bot_url'                   => Setting::get('wa_bot_url', config('services.whatsapp_bot.url', 'http://127.0.0.1:3789')),
            'wa_bot_api_key'               => Setting::get('wa_bot_api_key', config('services.whatsapp_bot.api_key', 'sipintu_wa_secret_key_2026')),
            'wa_notify_piket_bertugas'     => (bool) Setting::get('wa_notify_piket_bertugas', true),
            'wa_notify_koordinator_piket'  => (bool) Setting::get('wa_notify_koordinator_piket', true),
            'wa_notify_siswa_terlambat'    => (bool) Setting::get('wa_notify_siswa_terlambat', true),
            'wa_notify_laporan_guru_piket' => (bool) Setting::get('wa_notify_laporan_guru_piket', true),
            'wa_active_days'               => json_decode(Setting::get('wa_active_days', json_encode([1, 2, 3, 4, 5])), true) ?: [1, 2, 3, 4, 5],
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
        $current = (bool) Setting::get('wa_bot_enabled', config('services.whatsapp_bot.enabled', true));
        $newStatus = ! $current;
        Setting::set('wa_bot_enabled', $newStatus);

        $statusText = $newStatus ? 'diaktifkan (ON)' : 'dinonaktifkan (OFF)';
        return back()->with('success', "Master WhatsApp Bot berhasil {$statusText}.");
    }

    /**
     * Simpan Pengaturan Bot WhatsApp ke Database Setting
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'wa_bot_url'     => 'required|url',
            'wa_bot_api_key' => 'required|string|max:255',
        ], [
            'wa_bot_url.required' => 'URL Server Bot wajib diisi.',
            'wa_bot_url.url'      => 'Format URL Server Bot tidak valid.',
            'wa_bot_api_key.required' => 'API Key Server Bot wajib diisi.',
        ]);

        Setting::set('wa_bot_url', rtrim($validated['wa_bot_url'], '/'));
        Setting::set('wa_bot_api_key', $validated['wa_bot_api_key']);
        Setting::set('wa_bot_enabled', $request->boolean('wa_bot_enabled'));
        Setting::set('wa_notify_piket_bertugas', $request->boolean('wa_notify_piket_bertugas'));
        Setting::set('wa_notify_koordinator_piket', $request->boolean('wa_notify_koordinator_piket'));
        Setting::set('wa_notify_siswa_terlambat', $request->boolean('wa_notify_siswa_terlambat'));
        Setting::set('wa_notify_laporan_guru_piket', $request->boolean('wa_notify_laporan_guru_piket'));

        if ($request->has('wa_active_days')) {
            $activeDays = array_map('intval', (array) $request->input('wa_active_days'));
            Setting::set('wa_active_days', json_encode(array_values($activeDays)));
        }

        return back()->with('success', 'Pengaturan WhatsApp Bot berhasil diperbarui.');
    }

    /**
     * Putuskan Sesi WhatsApp (Logout & hapus auth folder) untuk scan nomor baru
     */
    public function disconnect()
    {
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
     * Restart Socket Baileys
     */
    public function restart()
    {
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
     * Ambil status detail dan QR code dari server Baileys
     */
    private function fetchBotStatus(): array
    {
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