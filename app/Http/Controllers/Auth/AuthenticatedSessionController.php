<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function showAdminLogin(): View
    {
        return view('auth.login-admin');
    }

    public function showSuperAdminLogin(): View
    {
        return view('auth.login-superadmin');
    }

    public function storeAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            Auth::logout();
            return redirect()->route('login.admin')
                ->withErrors(['email' => 'Akun ini tidak memiliki akses ke panel admin.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function storeSuperAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            Auth::logout();
            return redirect()->route('login.superadmin')
                ->withErrors(['email' => 'Akun ini bukan Super Admin.']);
        }

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
}
