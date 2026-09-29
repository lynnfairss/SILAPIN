<?php

namespace App\Services;

use App\Models\Inventaris;

/**
 * Pengelola stok inventaris.
 *
 * `inventaris.stok` berarti STOK TERSEDIA, bukan jumlah fisik. Stok berkurang
 * saat permohonan disetujui dan bertambah lagi saat barang dikembalikan.
 *
 * Semua pengurangan harus lewat kurang(): keputusan diambil database dalam
 * satu pernyataan, sehingga dua permintaan bersamaan tidak bisa sama-sama
 * mengambil unit terakhir. Memeriksa "stok cukup" terpisah lalu menulis
 * akan terkena race condition dan bisa membiarkan stok menjadi minus.
 */
class StokService
{
    /**
     * Kurangi stok secara atomik. Mengembalikan false bila stok tidak cukup
     * atau baris inventaris tidak ada.
     */
    public function kurang(int $inventarisId, int $jumlah): bool
    {
        if ($jumlah < 1) {
            return true;
        }

        $terkunci = Inventaris::where('id', $inventarisId)
            ->where('stok', '>=', $jumlah)
            ->decrement('stok', $jumlah);

        return $terkunci === 1;
    }

    /**
     * Kembalikan stok. Dipanggil hanya dari satu jalur (pengembalian) yang
     * dijaga StatusPermohonan agar tidak berjalan dua kali.
     */
    public function tambah(int $inventarisId, int $jumlah): void
    {
        if ($jumlah < 1) {
            return;
        }

        Inventaris::where('id', $inventarisId)->increment('stok', $jumlah);
    }

    /**
     * Kembalikan true bila stok tersedia masih menutup permintaan.
     */
    public function cukup(int $inventarisId, int $jumlah): bool
    {
        return (int) Inventaris::where('id', $inventarisId)->value('stok') >= $jumlah;
    }
}
