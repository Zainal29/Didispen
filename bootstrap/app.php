<?php

use App\Http\Middleware\CheckDispensasiTime;
use App\Http\Middleware\GuruPiketMiddleware;
use App\Http\Middleware\MustChangePassword;
use App\Http\Middleware\PrintLimitMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. DAFTARKAN MIDDLEWARE ALIAS DI SINI
        // Ini menggantikan fungsi $routeMiddleware di app/Http/Kernel.php versi lama
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'guru.piket' => GuruPiketMiddleware::class,
            'print.limit' => PrintLimitMiddleware::class,
            'must.change.password' => MustChangePassword::class,

            // ✅ BARU: Middleware untuk membatasi waktu pengajuan dispensasi
            'check.dispensasi.time' => CheckDispensasiTime::class,
        ]);

        // 2. (Opsional) Menambahkan middleware ke group web secara global
        // $middleware->web(append: [
        //     \App\Http\Middleware\EnsureEmailIsVerified::class,
        // ]);

        // 3. (Opsional) Mengecualikan URI tertentu dari CSRF protection
        // $middleware->validateCsrfTokens(except: [
        //     'stripe/*',
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Opsional: Custom error handling jika diperlukan
        // $exceptions->render(function (AuthenticationException $e, Request $request) {
        //     return $request->is('api/*') ? response()->json(['message' => $e->getMessage()], 401) : redirect()->guest(route('login'));
        // });
    })
    ->create();
