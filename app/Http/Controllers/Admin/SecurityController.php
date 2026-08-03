<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use LaravelWebauthn\Models\WebauthnKey;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class SecurityController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $passkeys = WebauthnKey::where('user_id', $user->getAuthIdentifier())
            ->orderByDesc('created_at')
            ->get();

        return response(view('admin.security', [
            'user' => $user,
            'passkeys' => $passkeys,
        ]))->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()->with('success', 'Autentikasi dua faktor berhasil diaktifkan.');
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
        ])->save();

        return back()->with('success', 'Autentikasi dua faktor berhasil dinonaktifkan.');
    }
}
