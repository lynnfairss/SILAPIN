<?php

namespace App\Services;

/**
 * Peta transisi status permohonan beserta efeknya terhadap stok.
 *
 * Tanpa peta ini, Admin\PermohonanController::updateStatus menerima status
 * apa pun dari status apa pun, sehingga combo "ubah status menjadi Disetujui"
 * lalu "proses pengembalian" bisa menambah stok berulang kali.
 */
class TransisiStatus
{
    public const MENUNGGU = 'Menunggu';

    public const DISETUJUI = 'Disetujui';

    public const DITOLAK = 'Ditolak';

    public const DIPINJAM = 'Dipinjam';

    public const DIKEMBALIKAN = 'Dikembalikan';

    public const SEMUA = [
        self::MENUNGGU,
        self::DISETUJUI,
        self::DITOLAK,
        self::DIPINJAM,
        self::DIKEMBALIKAN,
    ];

    /**
     * Status yang sedang memegang barang. Permohonan pada status ini tidak
     * boleh dihapus, dan barangnya tidak boleh hilang dari inventaris.
     */
    public const SEDANG_DIPAKAI = [self::DISETUJUI, self::DIPINJAM];

    /**
     * Efek stok per perpindahan status:
     *   'kurang' = barang keluar dari gudang
     *   'tambah' = barang kembali ke gudang
     *   null     = tidak menyentuh stok
     */
    private const PETA = [
        self::MENUNGGU => [
            self::DISETUJUI => 'kurang',
            self::DITOLAK => null,
        ],
        self::DISETUJUI => [
            self::DIPINJAM => null,
            self::DIKEMBALIKAN => 'tambah',
        ],
        self::DIPINJAM => [
            self::DIKEMBALIKAN => 'tambah',
        ],
        self::DITOLAK => [],
        self::DIKEMBALIKAN => [],
    ];

    public static function boleh(string $dari, string $ke): bool
    {
        // Peta BERKUNCI status tujuan, jadi harus pakai array_key_exists.
        // in_array() di sini akan mencocokkan nilainya ('kurang' / null).
        return array_key_exists($ke, self::PETA[$dari] ?? []);
    }

    public static function efekStok(string $dari, string $ke): ?string
    {
        if (! self::boleh($dari, $ke)) {
            throw TransisiStatusTidakValid::dari($dari, $ke);
        }

        return self::PETA[$dari][$ke];
    }

    /**
     * @return list<string>
     */
    public static function tujuan(string $dari): array
    {
        return array_keys(self::PETA[$dari] ?? []);
    }
}
