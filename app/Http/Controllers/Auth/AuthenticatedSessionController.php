<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const NO_STORE = 'no-store, no-cache, must-revalidate, max-age=0';

    public function showAdminLogin(): Response
    {
        return response(view('auth.login-admin'))
            ->header('Cache-Control', self::NO_STORE);
    }

    public function showSuperAdminLogin(): Response
    {
        return response(view('auth.login-superadmin'))
            ->header('Cache-Control', self::NO_STORE);
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $user = $this->verifyCredentials($request);

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            return redirect()->route('login.admin')
                ->withErrors(['email' => 'Akun ini tidak memiliki akses ke panel admin.']);
        }

        return $this->handleTwoFactorOrLogin($request, $user);
    }

    public function storeSuperAdmin(LoginRequest $request): RedirectResponse
    {
        $user = $this->verifyCredentials($request);

        if (! $user->isSuperAdmin()) {
            return redirect()->route('login.superadmin')
                ->withErrors(['email' => 'Akun ini bukan Super Admin.']);
        }

        return $this->handleTwoFactorOrLogin($request, $user);
    }

    public function showTwoFactor(Request $request): Response
    {
        return response(view('auth.two-factor', [
            'expired' => ! $request->session()->has('2fa_user_id'),
        ]))->header('Cache-Control', self::NO_STORE);
    }

    public function storeTwoFactor(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->session()->has('2fa_user_id')
            ? User::find($request->session()->get('2fa_user_id'))
            : null;

        if (! $user || ! $user->twoFactorEnabled()) {
            $request->session()->forget('2fa_user_id');

            return redirect()->route('login.admin')
                ->withErrors(['email' => 'Sesi verifikasi dua faktor tidak valid. Silakan masuk kembali.']);
        }

        if (! $user->verifyTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi salah. Silakan coba lagi.']);
        }

        $remember = (bool) $request->session()->pull('2fa_remember', false);
        $request->session()->forget('2fa_user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function verifyCredentials(LoginRequest $request): User
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::validate($credentials)) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        return User::where('email', $request->email)->firstOrFail();
    }

    private function handleTwoFactorOrLogin(LoginRequest $request, User $user): RedirectResponse
    {
        if ($user->twoFactorEnabled()) {
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));

            return redirect()->route('login.2fa');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
