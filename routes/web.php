<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\GuruPiketController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru;
use App\Http\Controllers\Guru\CetakStrukController;
use App\Http\Controllers\Guru\ChecklogController;
use App\Http\Controllers\Guru\PengajuanController as GuruPengajuanController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Satpam\DashboardController;
use App\Http\Controllers\Satpam\ScanController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\Siswa;
use App\Http\Controllers\Siswa\CetakController;
use App\Http\Controllers\Siswa\NotifikasiController;
use App\Http\Controllers\Siswa\PengajuanController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login
Route::get('/', fn () => redirect()->route('login'));

// Endpoint Health Check untuk SiPintu Gateway
Route::get('/health', fn () => response()->json(['status' => 'ok']));

// =========================================================================
// JALUR 1: CALLBACK SSO SIPINTU (MODEL HYBRID)
// =========================================================================
Route::get('/oauth/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
// Route Auth (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    // Rate limit login dilonggarkan ke 120/menit per IP untuk mengakomodasi banyak user dari satu Wi-Fi/NAT sekolah
    // Proteksi brute-force per akun tetap ditangani mandiri oleh LoginController (lockout 15 menit jika 5x gagal)
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:120,1')
        ->name('login.post');
});

// Logout (Authenticated)
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ==========================================
// GROUP AUTHENTICATED (Semua User Login)
// ==========================================
Route::middleware(['auth'])->group(function () {
    Route::get('/keamanan/lengkapi-profil', [SecurityController::class, 'showPrompt'])->name('security.prompt');
    Route::post('/keamanan/lengkapi-profil', [SecurityController::class, 'updateProfile'])->name('security.update');
    Route::post('/keamanan/cek-sipintu', [SecurityController::class, 'checkSipintuStatus'])->name('security.check-sipintu');



    // ==========================================
    // PROFIL (Shared Layout, Logic Berbeda per Role)
    // ==========================================
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');

        // Siswa & Satpam: Update Data Tambahan / Kontak WA
        Route::post('/update-additional', [ProfileController::class, 'updateAdditional'])
            ->middleware('role:siswa,satpam')
            ->name('update-additional');

        // Khusus Admin: Ganti Password
        Route::match(['put', 'post'], '/update-password', [ProfileController::class, 'updatePassword'])
            ->middleware('role:admin')
            ->name('update-password');


            // ✅ BARU: Logout Perangkat Lain
            Route::post('/logout-other-devices', [ProfileController::class, 'logoutOtherDevices'])
                ->name('logout-other-devices');

            // ✅ BARU: Hapus Riwayat Login (Role: Admin, Siswa, Guru, Satpam)
            Route::delete('/riwayat-login', [ProfileController::class, 'clearLoginHistory'])
                ->name('clear-login-history');
            Route::delete('/riwayat-login/{auditLog}', [ProfileController::class, 'destroyLoginHistory'])
                ->name('destroy-login-history');

    });

            // ==========================================
            // ADMIN ROUTES
            // ==========================================
            Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

                Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

                Route::patch('siswa/{siswa}/toggle-superadmin', [Admin\SiswaController::class, 'toggleSuperadmin'])->name('siswa.toggle-superadmin');
                Route::resource('siswa', Admin\SiswaController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);

                // ✅ KELOLA SUPER ADMIN (Dual-Role Siswa / Admin)
                Route::get('superadmin', [Admin\SuperAdminController::class, 'index'])->name('superadmin.index');
                Route::post('superadmin', [Admin\SuperAdminController::class, 'store'])->name('superadmin.store');
                Route::delete('superadmin/{user}', [Admin\SuperAdminController::class, 'destroy'])->name('superadmin.destroy');
                Route::get('api/superadmin/search-siswa', [Admin\SuperAdminController::class, 'searchSiswa'])->name('superadmin.search-siswa');
                Route::resource('guru', Admin\GuruController::class)->only(['index', 'store', 'update', 'destroy']);
                Route::resource('piket', GuruPiketController::class)->only(['index', 'store', 'update', 'destroy']);
                Route::patch('jadwal-piket/{jadwal}/toggle', [Admin\JadwalPiketController::class, 'toggle'])->name('jadwal-piket.toggle');
                Route::resource('jadwal-piket', Admin\JadwalPiketController::class)->parameters(['jadwal-piket' => 'jadwal']);
                Route::resource('satpam', Admin\SatpamController::class)->only(['index', 'store', 'update', 'destroy']);

                // ✅ SEMUA PENGAJUAN (Dispensasi) - Soft Delete, Restore, Force Delete
                Route::get('semua-pengajuan', [Admin\DispensasiController::class, 'index'])->name('semua.pengajuan');
                Route::get('semua-pengajuan/{id}', [Admin\DispensasiController::class, 'show'])->name('semua.pengajuan.show');
                Route::delete('semua-pengajuan/{dispensasi}', [Admin\DispensasiController::class, 'destroy'])->name('semua.pengajuan.destroy');
                Route::post('semua-pengajuan/{id}/restore', [Admin\DispensasiController::class, 'restore'])->name('semua.pengajuan.restore');
                Route::delete('semua-pengajuan/{id}/force-delete', [Admin\DispensasiController::class, 'forceDelete'])->name('semua.pengajuan.force-delete');

                Route::get('laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
                Route::get('laporan/pdf', [Admin\LaporanController::class, 'exportPdf'])->name('laporan.pdf'); // ✅ PASTIKAN ADA
                Route::get('laporan/excel', [Admin\LaporanController::class, 'exportExcel'])->name('laporan.excel'); // ✅ PASTIKAN ADA

                Route::get('pengaturan', [Admin\SettingsController::class, 'index'])->name('settings.index');
                Route::put('pengaturan', [Admin\SettingsController::class, 'update'])->name('settings.update');
                Route::get('audit-log', [Admin\AuditLogController::class, 'index'])->name('audit.index');
                Route::delete('audit-log/clear', [Admin\AuditLogController::class, 'clear'])->name('audit.clear');
                Route::delete('audit-log/{auditLog}', [Admin\AuditLogController::class, 'destroy'])->name('audit.destroy');
                Route::get('guru/checklog', [Admin\GuruController::class, 'checklog'])->name('guru.checklog');
                Route::delete('guru/checklog/{log}', [Admin\GuruController::class, 'destroyChecklog'])->name('guru.checklog.destroy');

                // Sinkronisasi SiPintu Gateway
                Route::post('sipintu/sync-siswa', [Admin\SipintuSyncController::class, 'syncSiswa'])->name('sipintu.sync-siswa');
                Route::post('sipintu/sync-guru', [Admin\SipintuSyncController::class, 'syncGuru'])->name('sipintu.sync-guru');

                // wa template
                Route::resource('whatsapp-templates', Admin\WhatsappTemplateController::class)->except(['show', 'create', 'edit']);
                Route::post('whatsapp-templates/preview', [Admin\WhatsappTemplateController::class, 'preview'])->name('whatsapp-templates.preview');

               
                // ✅ Bot Baileys & Log Pengiriman WhatsApp
                Route::prefix('whatsapp-bot')->name('whatsapp-bot.')->group(function () {
                    Route::get('/', [Admin\WhatsappBotController::class, 'index'])->name('index');
                    Route::get('/status', [Admin\WhatsappBotController::class, 'statusAjax'])->name('status');
                    Route::post('/toggle', [Admin\WhatsappBotController::class, 'toggle'])->name('toggle');
                    Route::post('/settings', [Admin\WhatsappBotController::class, 'updateSettings'])->name('settings');
                    Route::post('/disconnect', [Admin\WhatsappBotController::class, 'disconnect'])->name('disconnect');
                    Route::post('/restart', [Admin\WhatsappBotController::class, 'restart'])->name('restart');
                    Route::post('/send-test', [Admin\WhatsappBotController::class, 'sendTest'])->name('send-test');
                    Route::post('/retry-all', [Admin\WhatsappBotController::class, 'retryAllFailed'])->name('retry-all');
                    Route::post('/{log}/retry', [Admin\WhatsappBotController::class, 'retry'])->name('retry');
                });

                // test route untuk cek koneksi ke SiPintu Gateway
                // Ubah dari Route::get menjadi Route::post
                 Route::post('sipintu/test-sync', [Admin\SipintuSyncController::class, 'testSync'])
                ->name('sipintu.test-sync');

                 Route::resource('tutorial-videos', Admin\TutorialVideoController::class);

                // Laporan Temuan Satpam
                Route::get('laporan-satpam', [Admin\LaporanSatpamController::class, 'index'])->name('laporan-satpam.index');
                Route::post('laporan-satpam/{laporanSatpam}/tindak-lanjuti', [Admin\LaporanSatpamController::class, 'tindakLanjuti'])->name('laporan-satpam.tindak-lanjuti');
                Route::delete('laporan-satpam/{laporanSatpam}', [Admin\LaporanSatpamController::class, 'destroy'])->name('laporan-satpam.destroy');
            });


    // ==========================================
    // GURU ROUTES
    // ==========================================
    Route::middleware(['role:guru'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');

        // 1. ROUTE SPESIFIK (HARUS DI ATAS!)
        Route::get('pengajuan/buat', [GuruPengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('pengajuan', [GuruPengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('pengajuan/search-siswa', [GuruPengajuanController::class, 'searchSiswa'])->name('pengajuan.search-siswa');

        // 2. ROUTE INDEX (DAFTAR)
        Route::get('pengajuan', [GuruPengajuanController::class, 'index'])->name('pengajuan.index');

        // 3. ROUTE DENGAN PARAMETER (HARUS DI BAWAH!)
        Route::get('pengajuan/{dispensasi}', [GuruPengajuanController::class, 'show'])->name('pengajuan.show');
        Route::post('pengajuan/{dispensasi}/approve', [GuruPengajuanController::class, 'approve'])->name('pengajuan.approve');
        Route::post('pengajuan/{dispensasi}/reject', [GuruPengajuanController::class, 'reject'])->name('pengajuan.reject');

        // Cetak
        Route::get('pengajuan/{dispensasi}/cetak-struk', [CetakStrukController::class, 'index'])
            ->middleware('print.limit')
            ->name('cetak-struk');

        Route::get('pengajuan/{dispensasi}/cetak-pdf', [CetakStrukController::class, 'exportPdf'])
            ->middleware('print.limit')
            ->name('cetak-pdf');

        // Laporan
        Route::get('laporan', [Guru\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/pdf', [Guru\LaporanController::class, 'exportPdf'])->name('laporan.pdf');
        Route::get('laporan/excel', [Guru\LaporanController::class, 'exportExcel'])->name('laporan.excel');

        // Checklog
        Route::get('checklog', [ChecklogController::class, 'index'])->name('checklog.index');
        Route::post('checklog', [ChecklogController::class, 'store'])->name('checklog.store');
        Route::post('checklog/{log}/checkin', [ChecklogController::class, 'checkIn'])->name('checklog.checkin');

        // Warning System
        Route::post('warning/{dispensasi}/send', [Guru\WarningController::class, 'sendWarning'])
            ->name('warning.send');

            // Scan QR Guru - Dilonggarkan ke 300/menit agar antrean rombongan/double frame check tidak terblokir
            Route::get('scan', [\App\Http\Controllers\Guru\ScanController::class, 'index'])->name('scan');
            Route::post('scan/verify', [\App\Http\Controllers\Guru\ScanController::class, 'verify'])
                ->middleware('throttle:300,1')
                ->name('scan.verify');
            Route::post('search-dispensasi', [\App\Http\Controllers\Guru\ScanController::class, 'searchDispensasi'])
                ->name('search-dispensasi');
            Route::post('konfirmasi/{dispensasi}/keluar', [\App\Http\Controllers\Guru\ScanController::class, 'konfirmasiKeluar'])
                ->name('konfirmasi.keluar');
            Route::post('konfirmasi/{dispensasi}/kembali', [\App\Http\Controllers\Guru\ScanController::class, 'konfirmasiKembali'])
                ->name('konfirmasi.kembali');

          // ✅ TAMBAHKAN INI: Route untuk tandai sudah dihubungi via WA
         Route::post('dispensasi/{dispensasi}/wa-contacted', [\App\Http\Controllers\Guru\DashboardController::class, 'markWaContacted'])
             ->name('dispensasi.wa-contacted');

        // Pertukaran / Penggantian Jadwal Piket
        Route::get('piket/swap/create', [\App\Http\Controllers\Guru\PiketSwapController::class, 'create'])->name('piket.swap.create');
        Route::post('piket/swap', [\App\Http\Controllers\Guru\PiketSwapController::class, 'store'])->name('piket.swap.store');
        Route::get('piket/swap/incoming', [\App\Http\Controllers\Guru\PiketSwapController::class, 'incoming'])->name('piket.swap.incoming');
        Route::post('piket/swap/{pertukaran}/accept', [\App\Http\Controllers\Guru\PiketSwapController::class, 'accept'])->name('piket.swap.accept');
        Route::post('piket/swap/{pertukaran}/reject', [\App\Http\Controllers\Guru\PiketSwapController::class, 'reject'])->name('piket.swap.reject');
        Route::post('piket/swap/{pertukaran}/cancel', [\App\Http\Controllers\Guru\PiketSwapController::class, 'cancel'])->name('piket.swap.cancel');

        // Laporan Temuan Satpam (Panel Guru Piket)
        Route::get('laporan-satpam', [\App\Http\Controllers\Guru\LaporanSatpamController::class, 'index'])->name('laporan-satpam.index');
        Route::post('laporan-satpam/{laporanSatpam}/tindak-lanjuti', [\App\Http\Controllers\Guru\LaporanSatpamController::class, 'tindakLanjuti'])->name('laporan-satpam.tindak-lanjuti');
    });

    // ==========================================
    // SISWA ROUTES
    // ==========================================
    Route::middleware(['role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', Siswa\DashboardController::class)->name('dashboard');

        Route::get('pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('pengajuan/buat', [PengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('api/cari-siswa', [PengajuanController::class, 'cariSiswa'])->name('api.cari-siswa');
        Route::get('api/cari-guru', [PengajuanController::class, 'cariGuru'])->name('api.cari-guru');
        Route::get('pengajuan/{dispensasi}', [PengajuanController::class, 'show'])->name('pengajuan.show');
        Route::get('qr-code/{dispensasi}', [PengajuanController::class, 'getQRCode'])->name('qr-code');

        Route::get('cetak/{dispensasi}', [CetakController::class, 'cetak'])->name('cetak');

        Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.read');
        Route::post('notifikasi/read-all', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.readAll');
        Route::delete('notifikasi/clear-all', [NotifikasiController::class, 'clearAll'])->name('notifikasi.clearAll');
        Route::delete('notifikasi/{notifikasi}', [NotifikasiController::class, 'destroy'])->name('notifikasi.destroy');

        Route::post('pengajuan/{dispensasi}/upload-foto-bukti', [\App\Http\Controllers\Siswa\PengajuanController::class, 'uploadFotoBukti'])->name('pengajuan.upload-foto-bukti');
        Route::delete('pengajuan/{dispensasi}/hapus-foto-bukti', [\App\Http\Controllers\Siswa\PengajuanController::class, 'hapusFotoBukti'])->name('pengajuan.hapus-foto-bukti');
        Route::post('pengajuan/{dispensasi}/batal', [\App\Http\Controllers\Siswa\PengajuanController::class, 'batalKeluar'])->name('pengajuan.batal');
    
        Route::get(
            'pengajuan/{dispensasi}/hubungi-guru-piket',
            [PengajuanController::class, 'hubungiGuruPiket']
        )->name('pengajuan.hubungi-guru-piket');
    });

    // ==========================================
    // SATPAM ROUTES
    // ==========================================
    Route::middleware(['role:satpam'])->prefix('satpam')->name('satpam.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('scan', [ScanController::class, 'index'])->name('scan');
        Route::post('scan/verify', [ScanController::class, 'verify'])->middleware('throttle:300,1')->name('scan.verify');

        // Konfirmasi Manual
        Route::post('konfirmasi/{dispensasi}/keluar', [DashboardController::class, 'konfirmasiKeluar'])->name('konfirmasi.keluar');
        Route::post('konfirmasi/{dispensasi}/kembali', [DashboardController::class, 'konfirmasiKembali'])->name('konfirmasi.kembali');

        Route::post('search-dispensasi', [\App\Http\Controllers\Satpam\DashboardController::class, 'searchDispensasi'])
            ->name('search-dispensasi');

        // Detail & Lainnya
        Route::get('dispensasi/{dispensasi}/detail', [DashboardController::class, 'showDetail'])->name('dispensasi.detail');
        Route::post('dispensasi/{dispensasi}/wa-contacted', [DashboardController::class, 'markWaContacted'])->name('wa-contacted');

        // Laporan Gerbang Satpam (Temuan / Siswa Tidak Kembali)
        Route::get('laporan', [\App\Http\Controllers\Satpam\LaporanController::class, 'index'])->name('laporan.index');
        Route::post('laporan', [\App\Http\Controllers\Satpam\LaporanController::class, 'store'])->name('laporan.store');
        Route::delete('laporan/{laporanSatpam}', [\App\Http\Controllers\Satpam\LaporanController::class, 'destroy'])->name('laporan.destroy');
        Route::get('laporan/search-siswa', [\App\Http\Controllers\Satpam\LaporanController::class, 'searchSiswa'])->name('laporan.search-siswa');
    });

    // ==========================================
    // PANDUAN PENGGUNAAN (Akses Semua User Login)
    // ==========================================
    Route::get('/panduan', fn () => view('panduan.index'))->name('panduan');

    // ==========================================
    // TESTING & DEBUG ROUTES
    // ==========================================
    Route::get('/cek-jam-server', function () {
        return response()->json([
            'php_now_app_timezone' => now()->toDateTimeString(),
            'app_timezone_config' => config('app.timezone'),
            'php_now_utc' => now('UTC')->toDateTimeString(),
            'php_ini_date_timezone' => ini_get('date.timezone'),
            'unix_timestamp' => time(),
        ]);
    })->middleware('role:admin');
});
