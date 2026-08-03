<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use LaravelWebauthn\Services\Webauthn;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Webauthn::ignoreRoutes();
    }

    public function boot(): void
    {
        Gate::define('is-super-admin', function ($user) {
            return $user->isSuperAdmin();
        });
    }
}
