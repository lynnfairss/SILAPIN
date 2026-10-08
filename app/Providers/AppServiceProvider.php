<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use LaravelWebauthn\Services\Webauthn;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Webauthn::ignoreRoutes();

        // CheckOrigin menolak origin http:// kecuali rpId terdaftar di
        // securedRelyingPartyId. Tanpa ini, register/login passkey lewat
        // http://localhost:8000 (dev desktop) gagal "HTTPS required".
        $this->app->afterResolving(
            CeremonyStepManagerFactory::class,
            function (CeremonyStepManagerFactory $factory): void {
                $factory->setSecuredRelyingPartyId(['localhost', '127.0.0.1', '[::1]']);
            }
        );
    }

    public function boot(): void
    {
        Gate::define('is-super-admin', function ($user) {
            return $user->isSuperAdmin();
        });
    }
}
