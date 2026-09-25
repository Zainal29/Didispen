<?php

use App\Http\Middleware\CheckDispensasiTime;
use App\Http\Middleware\GuruPiketMiddleware;
// use App\Http\Middleware\MustChangePassword;
use App\Http\Middleware\PrintLimitMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession; // ✅ TAMBAHKAN INI

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. DAFTARKAN MIDDLEWARE ALIAS
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'guru.piket' => GuruPiketMiddleware::class,
            'print.limit' => PrintLimitMiddleware::class,
            'check.dispensasi.time' => CheckDispensasiTime::class,
        ]);

        // 2. ✅ AKTIFKAN AUTHENTICATE SESSION UNTUK FORCE LOGOUT
        $middleware->web(append: [
            AuthenticateSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })
    ->create();
