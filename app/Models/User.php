<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class User extends Authenticatable implements PasskeyUser
{
    use HasFactory, Notifiable, PasskeyAuthenticatable;

    public const RECOVERY_CODE_COUNT = 8;

    /** Tanpa 0/O/1/I supaya kode mudah dibaca ulang dari kertas. */
    private const RECOVERY_KARAKTER = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected $fillable = [
        'name',
        'email',
        'nip',
        'password',
        'role',
        'status',
        'two_factor_secret',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
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

        if (Google2FA::verifyKey($this->two_factor_secret, $code)) {
            return true;
        }

        return $this->consumeRecoveryCode($code);
    }

    /**
     * Buat satu set kode pemulihan baru. Kodenya dikembalikan sekali untuk
     * ditampilkan, sedangkan yang disimpan hanya hash-nya.
     *
     * @return list<string>
     */
    public function generateTwoFactorRecoveryCodes(): array
    {
        $polos = [];

        foreach (range(1, self::RECOVERY_CODE_COUNT) as $i) {
            $acak = '';

            foreach (range(1, 8) as $posisi) {
                $acak .= self::RECOVERY_KARAKTER[random_int(0, strlen(self::RECOVERY_KARAKTER) - 1)];
            }

            $polos[] = 'SLPN-' . substr($acak, 0, 4) . '-' . substr($acak, 4);
        }

        $this->two_factor_recovery_codes = array_map(
            fn (string $kode) => Hash::make($this->normalisasiKodePemulihan($kode)),
            $polos
        );

        return $polos;
    }

    public function recoveryCodeCount(): int
    {
        return count($this->two_factor_recovery_codes ?? []);
    }

    /**
     * Cocokkan dan habiskan satu kode pemulihan.
     */
    public function consumeRecoveryCode(string $code): bool
    {
        $normal = $this->normalisasiKodePemulihan($code);

        if ($normal === '' || ! $this->twoFactorEnabled()) {
            return false;
        }

        foreach ($this->two_factor_recovery_codes ?? [] as $index => $hash) {
            if (Hash::check($normal, $hash)) {
                $sisa = $this->two_factor_recovery_codes;
                unset($sisa[$index]);
                $this->two_factor_recovery_codes = array_values($sisa);
                $this->save();

                return true;
            }
        }

        return false;
    }

    private function normalisasiKodePemulihan(string $kode): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $kode));
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
