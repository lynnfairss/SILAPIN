<?php

namespace App\Services;

use App\Models\Permohonan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Pembuat nomor permohonan.
 *
 * Format: SP-<DDMMYY>-<8 karakter acak>, contoh SP-280926-K3M9QX4T.
 *
 * Nomor ini dicetak pada QR code dan dipindai admin saat pengembalian, jadi
 * formatnya harus pendek dan stabil. Acak yang dulu dipakai adalah
 * substr(uniqid(), -6) yang hanya menghasilkan 6 hex digit (16^6) dan tidak
 * pernah diperiksa bentroknya padahal kolomnya UNIQUE.
 */
class NomorPermohonan
{
    private const PREFIX = 'SP-';

    private const PANJANG_ACAK = 8;

    private const PERCOBAAN = 5;

    public static function generate(): string
    {
        $tanggal = Carbon::now()->format('dmy');

        for ($i = 0; $i < self::PERCOBAAN; $i++) {
            $nomor = self::PREFIX.$tanggal.'-'.strtoupper(Str::random(self::PANJANG_ACAK));

            if (! Permohonan::where('nomor_permohonan', $nomor)->exists()) {
                return $nomor;
            }
        }

        throw new \RuntimeException(
            'Gagal membuat nomor permohonan yang unik setelah '.self::PERCOBAAN.' percobaan.'
        );
    }
}
