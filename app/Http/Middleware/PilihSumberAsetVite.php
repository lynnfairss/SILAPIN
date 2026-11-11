<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Vite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Perangkat lain (HP di jaringan lokal) tidak bisa menjangkau dev server
 * Vite, yang hot file-nya menunjuk ke IPv6 localhost mesin ini. Untuk host
 * selain localhost, halaman dipakai dari hasil build di public/build supaya
 * CSS dan JS ikut terunduh.
 */
class PilihSumberAsetVite
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower($request->getHost());

        $mesinIni = in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]', 'silapin.local'], true);

        if (! $mesinIni) {
            app(Vite::class)->useHotFile(public_path('hot-dari-perangkat-lain'));
        }

        return $next($request);
    }
}
