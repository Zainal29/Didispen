        <?php

        use App\Http\Controllers\Admin;
        use App\Http\Controllers\Admin\GuruPiketController;
        use App\Http\Controllers\Auth\LoginController;
        use App\Http\Controllers\Guru;
        use App\Http\Controllers\Guru\CetakStrukController;
        use App\Http\Controllers\Guru\ChecklogController;
        use App\Http\Controllers\Guru\PengajuanController as GuruPengajuanController;
        use App\Http\Controllers\ProfileController;
        use App\Http\Controllers\Satpam;
        use App\Http\Controllers\Satpam\DashboardController;
        use App\Http\Controllers\Satpam\ScanController;
        use App\Http\Controllers\Siswa;
        use App\Http\Controllers\Siswa\CetakController;
        use App\Http\Controllers\Siswa\NotifikasiController;
        use App\Http\Controllers\Siswa\PengajuanController;
        use Illuminate\Support\Facades\Route;

        // ==========================================
        // PUBLIC ROUTES
        // ==========================================
        Route::get('/', fn () => redirect('/login'));
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        // Route publik untuk verifikasi QR Code via URL
        Route::get('/verify-qr/{dispensasi}', [\App\Http\Controllers\Satpam\ScanController::class, 'verifyQR'])
            ->name('verify.qr');

        // ✅ REVISI SIPINTU: Route wajib ganti password DIHAPUS.
        // Password dikelola oleh SiPintu/Sijuna. User yang lupa password
        // harus menghubungi admin pusat.

        // ==========================================
        // PROFIL (Shared Layout, Logic Berbeda per Role)
        // ==========================================
        Route::middleware(['auth'])->prefix('profil')->name('profil.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');

            // Khusus Siswa: Update Data Tambahan
            Route::post('/update-additional', [ProfileController::class, 'updateAdditional'])
                ->middleware('role:siswa')
                ->name('update-additional');

            // Khusus Admin: Ganti Password
            Route::post('/update-password', [ProfileController::class, 'updatePassword'])
                ->middleware('role:admin')
                ->name('update-password');
        });

        // ==========================================
        // ADMIN ROUTES
        // ==========================================
        Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

            Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

            Route::resource('siswa', Admin\SiswaController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
            Route::resource('guru', Admin\GuruController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('piket', GuruPiketController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('satpam', Admin\SatpamController::class)->only(['index', 'store', 'update', 'destroy']);

            Route::put('profil/update-password', [ProfileController::class, 'updatePassword'])->name('profil.update-password');

            // ✅ SEMUA PENGAJUAN (Dispensasi) - Termasuk Route Hapus
            Route::get('semua-pengajuan', [Admin\DispensasiController::class, 'index'])->name('semua.pengajuan');
            Route::get('semua-pengajuan/{dispensasi}', [Admin\DispensasiController::class, 'show'])->name('semua.pengajuan.show');
            Route::delete('semua-pengajuan/{dispensasi}', [Admin\DispensasiController::class, 'destroy'])->name('semua.pengajuan.destroy');

            Route::get('laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
            Route::get('laporan/pdf', [Admin\LaporanController::class, 'exportPdf'])->name('laporan.pdf');
            Route::get('laporan/excel', [Admin\LaporanController::class, 'exportExcel'])->name('laporan.excel');

            Route::get('pengaturan', [Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::put('pengaturan', [Admin\SettingsController::class, 'update'])->name('settings.update');
            Route::get('audit-log', [Admin\AuditLogController::class, 'index'])->name('audit.index');
            Route::get('guru/checklog', [Admin\GuruController::class, 'checklog'])->name('guru.checklog');


            // ✅ REVISI SIPINTU: Route manajemen password admin DIHAPUS.
                    // Admin TIDAK BISA melihat atau mereset password user karena
                    // password di-hash dan dikelola oleh SiPintu/Sijuna.
                    // Jika user lupa password, arahkan ke admin pusat SiPintu/Sijuna.
            // Sinkronisasi SiPintu Gateway
            Route::post('sipintu/sync-siswa', [Admin\SipintuSyncController::class, 'syncSiswa'])->name('sipintu.sync-siswa');
            Route::post('sipintu/sync-guru', [Admin\SipintuSyncController::class, 'syncGuru'])->name('sipintu.sync-guru');



        });
            // ==========================================
            // GURU ROUTES
            // ==========================================
            Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
                Route::get('dashboard', [\App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');

                // ==========================================
                // 1. ROUTE SPESIFIK (HARUS DI ATAS!)
                // ==========================================
                Route::get('pengajuan/buat', [GuruPengajuanController::class, 'create'])->name('pengajuan.create');
                Route::post('pengajuan', [GuruPengajuanController::class, 'store'])->name('pengajuan.store');
                Route::get('pengajuan/search-siswa', [GuruPengajuanController::class, 'searchSiswa'])->name('pengajuan.search-siswa'); // ✅ BARU


                // ==========================================
                // 2. ROUTE INDEX (DAFTAR)
                // ==========================================
                Route::get('pengajuan', [GuruPengajuanController::class, 'index'])->name('pengajuan.index');

                // ==========================================
                // 3. ROUTE DENGAN PARAMETER (HARUS DI BAWAH!)
                // ==========================================
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

                // Scan QR Backup
                Route::get('scan', [\App\Http\Controllers\Guru\ScanController::class, 'index'])->name('scan');
                Route::post('scan/verify', [\App\Http\Controllers\Guru\ScanController::class, 'verify'])->name('scan.verify');
            });

            // ==========================================
            // SISWA ROUTES
            // ==========================================
            Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
                Route::get('/dashboard', Siswa\DashboardController::class)->name('dashboard');

                Route::get('pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
                Route::get('pengajuan/buat', [PengajuanController::class, 'create'])->name('pengajuan.create');
                Route::post('pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
                Route::get('pengajuan/{dispensasi}', [PengajuanController::class, 'show'])->name('pengajuan.show');
                Route::get('qr-code/{dispensasi}', [PengajuanController::class, 'getQRCode'])->name('qr-code');

                Route::get('cetak/{dispensasi}', [CetakController::class, 'cetak'])->name('cetak');

                Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
                Route::post('notifikasi/{notifikasi}/read', [NotifikasiController::class, 'markRead'])->name('notifikasi.read');
                Route::post('notifikasi/read-all', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.readAll');

                // ✅ HAPUS BARIS INI (duplikat dengan group PROFIL di atas):
                // Route::get('/profil', [ProfileController::class, 'show'])->name('siswa.profil.show');
            });

        // ==========================================
        // SATPAM ROUTES
        // ==========================================
        Route::middleware(['auth', 'role:satpam'])->prefix('satpam')->name('satpam.')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('scan', [ScanController::class, 'index'])->name('scan');
            Route::post('scan/verify', [ScanController::class, 'verify'])->middleware('throttle:10,1')->name('scan.verify');

            // Konfirmasi Manual
            Route::post('konfirmasi/{dispensasi}/keluar', [DashboardController::class, 'konfirmasiKeluar'])->name('konfirmasi.keluar');
            Route::post('konfirmasi/{dispensasi}/kembali', [DashboardController::class, 'konfirmasiKembali'])->name('konfirmasi.kembali');


            // ✅ PERBAIKAN: Cukup 'search-dispensasi', karena sudah ada prefix 'satpam.' dari grup
            Route::post('search-dispensasi', [\App\Http\Controllers\Satpam\DashboardController::class, 'searchDispensasi'])
                ->name('search-dispensasi');

            // Detail & Lainnya
            Route::get('dispensasi/{dispensasi}/detail', [DashboardController::class, 'showDetail'])->name('dispensasi.detail');
            Route::post('dispensasi/{dispensasi}/mark-contacted', [DashboardController::class, 'markContacted'])->name('mark-contacted');
            Route::post('dispensasi/{dispensasi}/wa-contacted', [DashboardController::class, 'markWaContacted'])->name('wa-contacted');
        });

        // ==========================================
        // PANDUAN PENGGUNAAN (Akses Semua User Login)
        // ==========================================
        Route::get('/panduan', fn () => view('panduan.index'))->middleware(['auth'])->name('panduan');

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
        })->middleware(['auth', 'role:admin']);
