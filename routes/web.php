<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru;
use App\Http\Controllers\Guru\PengajuanController as GuruPengajuanController;
use App\Http\Controllers\Guru\CetakStrukController;
use App\Http\Controllers\Guru\PrintBluetoothController;
use App\Http\Controllers\Admin\GuruPiketController;
use App\Http\Controllers\Siswa;
use App\Http\Controllers\Satpam;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// ADMIN ROUTES
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');
    Route::resource('jurusan', Admin\JurusanController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('kelas', Admin\KelasController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('siswa', Admin\SiswaController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('guru', Admin\GuruController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('piket', Admin\GuruPiketController::class)->only(['index', 'store', 'destroy']);
  // ✅ Tambahkan 'update' agar form di view bisa mengirim data
    Route::resource('piket', Admin\GuruPiketController::class)->only(['index', 'update', 'destroy']);
    Route::resource('satpam', Admin\SatpamController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('semua-pengajuan', [Admin\DispensasiController::class, 'index'])->name('semua.pengajuan');
    Route::get('semua-pengajuan/{dispensasi}', [Admin\DispensasiController::class, 'show'])->name('semua.pengajuan.show');

    Route::get('laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/pdf', [Admin\LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('laporan/excel', [Admin\LaporanController::class, 'exportExcel'])->name('laporan.excel');

    Route::get('pengaturan', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('pengaturan', [Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::get('audit-log', [Admin\AuditLogController::class, 'index'])->name('audit.index');
    Route::get('guru/checklog', [Admin\GuruController::class, 'checklog'])->name('guru.checklog');
});

// ==========================================
// GURU ROUTES
// ==========================================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', Guru\DashboardController::class)->name('dashboard');

    // Pengajuan
    Route::get('pengajuan', [GuruPengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('pengajuan/{dispensasi}', [GuruPengajuanController::class, 'show'])->name('pengajuan.show');
    Route::post('pengajuan/{dispensasi}/approve', [GuruPengajuanController::class, 'approve'])->name('pengajuan.approve');
    Route::post('pengajuan/{dispensasi}/reject', [GuruPengajuanController::class, 'reject'])->name('pengajuan.reject');

    // ✅ PERBAIKAN: Hapus prefix '/guru/' karena sudah ada di group
    Route::get('pengajuan/{dispensasi}/cetak-struk', [CetakStrukController::class, 'index'])
        ->name('cetak-struk');

    // ✅ PERBAIKAN: Hapus prefix '/guru/' karena sudah ada di group
    Route::post('pengajuan/{dispensasi}/print-thermal', [PrintBluetoothController::class, 'print'])
        ->name('print-thermal');

    Route::get('/pengajuan/{dispensasi}/print-data', [PrintBluetoothController::class, 'getData'])
    ->name('guru.dispensasi.print-data');

    // Laporan
    Route::get('laporan', [Guru\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/pdf', [Guru\LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('laporan/excel', [Guru\LaporanController::class, 'exportExcel'])->name('laporan.excel');

    // Checklog
    Route::get('checklog', [\App\Http\Controllers\Guru\ChecklogController::class, 'index'])->name('checklog.index');
    Route::post('checklog', [\App\Http\Controllers\Guru\ChecklogController::class, 'store'])->name('checklog.store');
    Route::post('checklog/{log}/checkin', [\App\Http\Controllers\Guru\ChecklogController::class, 'checkIn'])->name('checklog.checkin');
});

// ==========================================
// SISWA ROUTES
// ==========================================
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', Siswa\DashboardController::class)->name('dashboard');

    Route::get('pengajuan', [\App\Http\Controllers\Siswa\PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('pengajuan/buat', [\App\Http\Controllers\Siswa\PengajuanController::class, 'create'])->name('pengajuan.create');
    Route::post('pengajuan', [\App\Http\Controllers\Siswa\PengajuanController::class, 'store'])->name('pengajuan.store');
    Route::get('pengajuan/{dispensasi}', [\App\Http\Controllers\Siswa\PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('qr-code/{dispensasi}', [Siswa\PengajuanController::class, 'getQRCode'])->name('qr-code');

    Route::get('cetak/{dispensasi}', [\App\Http\Controllers\Siswa\CetakController::class, 'cetak'])->name('cetak');

    Route::get('notifikasi', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('notifikasi/{notifikasi}/read', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'markAllRead'])->name('notifikasi.readAll');
});

// ==========================================
// SATPAM ROUTES
// ==========================================
Route::middleware(['auth', 'role:satpam'])->prefix('satpam')->name('satpam.')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Satpam\DashboardController::class, 'index'])->name('dashboard');
    Route::get('scan', [\App\Http\Controllers\Satpam\ScanController::class, 'index'])->name('scan');
    Route::post('scan/verify', [\App\Http\Controllers\Satpam\ScanController::class, 'verify'])->name('scan.verify');
    // Konfirmasi Manual
    Route::post('konfirmasi/{dispensasi}/keluar', [\App\Http\Controllers\Satpam\DashboardController::class, 'konfirmasiKeluar'])->name('konfirmasi.keluar');
    Route::post('konfirmasi/{dispensasi}/kembali', [\App\Http\Controllers\Satpam\DashboardController::class, 'konfirmasiKembali'])->name('konfirmasi.kembali');
});
// ==========================================
// PANDUAN PENGGUNAAN (Akses Semua User Login)
// ==========================================
Route::get('/panduan', fn() => view('panduan.index'))->middleware('auth')->name('panduan');


// ==========================================
// TESTING & DEBUG ROUTES (Tanpa Auth)
// ==========================================
Route::get('/cek-jam-server', function () {
    return response()->json([
        'php_now_app_timezone'   => now()->toDateTimeString(),
        'app_timezone_config'    => config('app.timezone'),
        'php_now_utc'            => now('UTC')->toDateTimeString(),
        'php_ini_date_timezone'  => ini_get('date.timezone'),
        'unix_timestamp'         => time(),
    ]);
});

// ✅ PERBAIKAN: Route test-printer dipindahkan ke luar closure yang salah
Route::get('/test-printer', function() {
    try {
        // Pastikan library escpos-php sudah diinstall
        if (!class_exists(\Mike42\Escpos\PrintConnectors\WindowsPrintConnector::class)) {
            return "❌ Library escpos-php belum terinstall. Jalankan: composer require mike42/escpos-php";
        }

        $connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector("POS-5809DD");
        $printer = new \Mike42\Escpos\Printer($connector);
        $printer->text("Test Print - Koneksi Berhasil!\n");
        $printer->cut();
        $printer->close();

        return "✅ Printer berhasil terhubung!";
    } catch (\Exception $e) {
        return "❌ Error: " . $e->getMessage();
    }
});
