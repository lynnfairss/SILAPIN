<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class SecurityController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $passkeys = $user->passkeys()
            ->orderByDesc('created_at')
            ->get();

        return response(view('admin.security', [
            'user' => $user,
            'passkeys' => $passkeys,
            'riwayat' => $this->riwayatPasskey($passkeys),
        ]))->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Ringkasan pemakaian passkey untuk kartu aktivitas. Sifat kunci dibaca dari
     * objek kredensial WebAuthn karena kolom terpisah tidak tersedia.
     */
    private function riwayatPasskey($passkeys): array
    {
        return $passkeys->map(function ($passkey) {
            // Kolom credential sudah di-cast array oleh model paket.
            $kredensial = (array) ($passkey->credential ?? []);
            $transports = (array) ($kredensial['transports'] ?? []);

            if (in_array('internal', $transports, true)) {
                $jenis = 'Kunci perangkat ini';
            } elseif (in_array('hybrid', $transports, true)) {
                $jenis = 'Sinkron antar perangkat';
            } elseif ($transports !== []) {
                $jenis = 'Kunci fisik';
            } else {
                $jenis = 'Tidak terdeteksi';
            }

            return [
                'nama' => $passkey->name,
                'autenticator' => $passkey->authenticator ?? 'Tidak terdeteksi',
                'jenis' => $jenis,
                'cadangan' => ! ($kredensial['backupEligible'] ?? false)
                    ? 'Tidak dapat dicadangkan'
                    : (($kredensial['backupStatus'] ?? false) ? 'Tercadangkan' : 'Belum tercadangkan'),
                'verifikasi' => ! empty($kredensial['uvInitialized']),
                'didaftarkan' => $passkey->created_at,
                'terpakai' => $passkey->last_used_at,
            ];
        })->sortByDesc(fn (array $baris) => $baris['terpakai']?->getTimestamp() ?? 0)
          ->values()
          ->all();
    }

    public function enableTwoFactor(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->twoFactorEnabled()) {
            return back()->with('error', 'Autentikasi dua faktor sudah aktif.');
        }

        $user->forceFill([
            'two_factor_secret' => Google2FA::generateSecretKey(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back();
    }

    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        if (is_null($user->two_factor_secret)) {
            return back()->with('error', 'Belum ada kunci autentikasi dua faktor.');
        }

        if (! $user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi salah.']);
        }

        $kodePemulihan = $user->generateTwoFactorRecoveryCodes();
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()
            ->with('success', 'Autentikasi dua faktor berhasil diaktifkan.')
            ->with('recovery_codes', $kodePemulihan);
    }

    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        if (! $user->twoFactorEnabled()) {
            return back()->with('error', 'Autentikasi dua faktor tidak aktif.');
        }

        if (! $user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi salah.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        return back()->with('success', 'Autentikasi dua faktor berhasil dinonaktifkan.');
    }

    /**
     * Buat ulang daftar kode pemulihan. Kode lama langsung tidak berlaku.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->twoFactorEnabled()) {
            return back()->with('error', 'Aktifkan autentikasi dua faktor terlebih dahulu.');
        }

        $kodePemulihan = $user->generateTwoFactorRecoveryCodes();
        $user->save();

        return back()
            ->with('success', 'Daftar kode pemulihan baru dibuat. Kode lama tidak berlaku.')
            ->with('recovery_codes', $kodePemulihan);
    }
}
