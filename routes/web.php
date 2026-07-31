<?php
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru;
use App\Http\Controllers\Siswa;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn() => redirect('/login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');
    Route::resource('jurusan', Admin\JurusanController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('kelas', Admin\KelasController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('siswa', Admin\SiswaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('guru', Admin\GuruController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('piket', Admin\GuruPiketController::class)->only(['index', 'store', 'destroy']);
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

// Guru Routes
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', Guru\DashboardController::class)->name('dashboard');
    Route::get('pengajuan', [Guru\PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('pengajuan/{dispensasi}', [Guru\PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::post('pengajuan/{dispensasi}/approve', [Guru\PengajuanController::class, 'approve'])->name('pengajuan.approve');
    Route::post('pengajuan/{dispensasi}/reject', [Guru\PengajuanController::class, 'reject'])->name('pengajuan.reject');
    Route::post('konfirmasi/{dispensasi}/keluar', [Guru\KonfirmasiController::class, 'keluar'])->name('konfirmasi.keluar');
    Route::post('konfirmasi/{dispensasi}/kembali', [Guru\KonfirmasiController::class, 'kembali'])->name('konfirmasi.kembali');
    Route::get('tanda-tangan', [Guru\TandaTanganController::class, 'index'])->name('tanda-tangan.index');
    Route::post('tanda-tangan', [Guru\TandaTanganController::class, 'store'])->name('tanda-tangan.store');
    Route::delete('tanda-tangan', [Guru\TandaTanganController::class, 'destroy'])->name('tanda-tangan.destroy');
    Route::get('laporan', [Guru\LaporanController::class, 'index'])->name('laporan.index');
        Route::get('checklog', [\App\Http\Controllers\Guru\ChecklogController::class, 'index'])->name('checklog.index');
    Route::post('checklog', [\App\Http\Controllers\Guru\ChecklogController::class, 'store'])->name('checklog.store');
    Route::post('checklog/{log}/checkin', [\App\Http\Controllers\Guru\ChecklogController::class, 'checkIn'])->name('checklog.checkin');
     Route::get('laporan', [Guru\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/pdf', [Guru\LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('laporan/excel', [Guru\LaporanController::class, 'exportExcel'])->name('laporan.excel');
    

});

// ==========================================
// SISWA ROUTES
// ==========================================
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', \App\Http\Controllers\Siswa\DashboardController::class)->name('dashboard');
    
    // Pengajuan Dispensasi
    Route::get('pengajuan', [\App\Http\Controllers\Siswa\PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('pengajuan/buat', [\App\Http\Controllers\Siswa\PengajuanController::class, 'create'])->name('pengajuan.create');
    Route::post('pengajuan', [\App\Http\Controllers\Siswa\PengajuanController::class, 'store'])->name('pengajuan.store');
    Route::get('pengajuan/{dispensasi}', [\App\Http\Controllers\Siswa\PengajuanController::class, 'show'])->name('pengajuan.show');
    
    // Cetak Surat
    // ✅ DIPERBAIKI: Middleware 'print.limit' dihapus untuk mencegah error ArgumentCountError.
    // Logika limitasi cetak (max 3 kali) sudah ditangani dengan lebih aman di dalam CetakController.
    Route::get('cetak/{dispensasi}', [\App\Http\Controllers\Siswa\CetakController::class, 'cetak'])->name('cetak');
    
    // Notifikasi
    Route::get('notifikasi', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('notifikasi/{notifikasi}/read', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [\App\Http\Controllers\Siswa\NotifikasiController::class, 'markAllRead'])->name('notifikasi.readAll');
});

 
Route::get('/cek-jam-server', function () {
    return response()->json([
        'php_now_app_timezone'   => now()->toDateTimeString(),
        'app_timezone_config'    => config('app.timezone'),
        'php_now_utc'            => now('UTC')->toDateTimeString(),
        'php_ini_date_timezone'  => ini_get('date.timezone'),
        'unix_timestamp'         => time(),
    ]);
});
 