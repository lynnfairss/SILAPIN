<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LaravelWebauthn\WebauthnAuthenticatable;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class User extends Authenticatable
{
    use HasFactory, Notifiable, WebauthnAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'two_factor_secret',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function twoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        if (is_null($this->two_factor_secret)) {
            return false;
        }

        return Google2FA::verifyKey($this->two_factor_secret, $code);
    }

    public function getTwoFactorQrCode(): string
    {
        return Google2FA::getQRCodeInline(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }
}
