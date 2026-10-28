<?php

namespace App\Providers;

use App\Exceptions\PasskeyLoginDeniedException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\Passkeys;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passkeys::useUserModel(User::class);
    }

    public function boot(): void
    {
        Gate::define('is-super-admin', function ($user) {
            return $user->isSuperAdmin();
        });

        Passkeys::authorizeLoginUsing(function (Request $request, ?PasskeyUser $user): bool {
            if (! $user instanceof User) {
                throw new PasskeyLoginDeniedException('Akun tidak dikenali.');
            }

            if ($user->status !== 'approved') {
                throw new PasskeyLoginDeniedException(
                    $user->status === 'rejected'
                        ? 'Akun ini telah ditolak. Hubungi Super Admin.'
                        : 'Akun ini belum disetujui. Hubungi Super Admin untuk mengaktifkan akses.'
                );
            }

            if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
                throw new PasskeyLoginDeniedException('Akun ini tidak memiliki akses ke panel admin.');
            }

            if ($request->header('X-Passkey-Role') === 'super_admin' && ! $user->isSuperAdmin()) {
                throw new PasskeyLoginDeniedException('Akun ini bukan Super Admin.');
            }

            return true;
        });
    }
}
