<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percaya proxy dev (dev-https.js / reverse proxy) agar skema
        // X-Forwarded-Proto: https dikenali (kamera & aset https).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'peminjam.access' => \App\Http\Middleware\VerifyPemohonanAccess::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'passkey/options',
            'passkey/login',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi telah berakhir. Muat ulang halaman lalu coba lagi.',
                ], 419);
            }

            if ($request->is('peminjam/*')) {
                return redirect()->route('peminjam.form')
                    ->with('error', 'Sesi telah berakhir atau token keamanan tidak valid. Silakan isi form kembali.');
            }

            return response()->view('errors.419', [], 419);
        });
    })->create();
