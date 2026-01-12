<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    // --- TEMPEL KODENYA DI SINI (BAGIAN INI) ---
    ->withMiddleware(function (Middleware $middleware) {

        // Trust ngrok proxy
        $middleware->trustProxies(at: '*');

        // Tambahkan middleware untuk cek user aktif pada setiap request web
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserActive::class,
        ]);

        // Daftarkan Alias Middleware Anda di sini
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

    })
    // -------------------------------------------
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();