<?php

namespace App\Http\Middleware;

use App\Models\Permohonan;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjaga berkas peminjam (surat DOCX/PDF/HTML).
 *
 * Dua sumber akses yang sah:
 *  1. Admin / Super Admin yang sudah login.
 *  2. Peminjam yang memegang token rahasia dari pengajuan atau cek-status.
 *
 * Route yang memakai middleware ini wajib menyertakan route model {permohonan}
 * supaya $request->route('permohonan') tersedia. Route tanpa model (cek-status)
 * memakai bolehLihatPii() di controller, karena yang perlu divalidasi adalah
 * berkas hasil pencarian, bukan parameter route.
 */
class VerifyPemohonanAccess
{
    public const PII_TERBUKA = 'peminjaman_pii_terbuka';

    /**
     * Apakah pemohon boleh melihat data pribadi berkas ini?
     */
    public static function bolehLihatPii(Request $request, ?Permohonan $permohonan): bool
    {
        if (self::adalahAdmin($request)) {
            return true;
        }

        return $permohonan !== null
            && $permohonan->tokenMatches($request->query('token'));
    }

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set(self::PII_TERBUKA, false);

        if (self::adalahAdmin($request)) {
            $request->attributes->set(self::PII_TERBUKA, true);

            return $next($request);
        }

        $permohonan = $request->route('permohonan');

        if ($permohonan instanceof Permohonan) {
            $boleh = $permohonan->tokenMatches($request->query('token'));

            $request->attributes->set(self::PII_TERBUKA, $boleh);

            if (! $boleh) {
                abort(403, 'Berkas ini tidak dapat diakses. Buka kembali halaman cek status dari perangkat yang Anda gunakan saat mengajukan.');
            }
        }

        return $next($request);
    }

    private static function adalahAdmin(Request $request): bool
    {
        $user = $request->user();

        return $user instanceof User && in_array($user->role, ['admin', 'super_admin'], true);
    }
}
