<?php

namespace App\Services;

use App\Models\Permohonan;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya jalur untuk mengubah status permohonan.
 *
 * Semua penulisan status harus lewat ubah() supaya tiga hal selalu benar:
 *  1. Status dikunci (lockForUpdate) sehingga dua permintaan bersamaan tidak
 *     bisa dua-duanya membaca "Dipinjam" lalu sama-sama mengembalikan barang.
 *  2. Perpindahan status divalidasi terhadap TransisiStatus.
 *  3. Efek stok mengikuti transisi, dan rollback bila gagal di tengah.
 */
class StatusPermohonan
{
    public function __construct(private readonly StokService $stok) {}

    /**
     * @param  array<string, mixed>  $atribut  kolom lain yang ikut ditulis
     * @param  string|null  $catatan  keterangan untuk riwayat status
     *
     * @throws TransisiStatusTidakValid saat perpindahan tidak sah
     * @throws StokTidakCukup bila stok tidak mencukupi
     */
    public function ubah(
        Permohonan $permohonan,
        string $statusBaru,
        array $atribut = [],
        ?string $catatan = null,
    ): Permohonan {
        return DB::transaction(function () use ($permohonan, $statusBaru, $atribut, $catatan) {
            $terkunci = Permohonan::whereKey($permohonan->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $statusLama = $terkunci->status;

            $efek = TransisiStatus::efekStok($statusLama, $statusBaru);

            $this->terapkanEfekStok($terkunci, $efek);

            $terkunci->fill($atribut);
            $terkunci->status = $statusBaru;
            $terkunci->save();

            $terkunci->statusLogs()->create([
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'catatan' => $catatan,
                'user_id' => auth()->id(),
            ]);

            return $terkunci;
        });
    }

    private function terapkanEfekStok(Permohonan $permohonan, ?string $efek): void
    {
        if ($efek === null) {
            return;
        }

        $permohonan->loadMissing('detailPermohonan.inventaris');

        foreach ($permohonan->detailPermohonan as $detail) {
            $jumlah = (int) $detail->jumlah;

            if ($efek === 'tambah') {
                $this->stok->tambah($detail->inventaris_id, $jumlah);

                continue;
            }

            if (! $this->stok->kurang($detail->inventaris_id, $jumlah)) {
                throw StokTidakCukup::untuk(
                    $detail->inventaris?->nama_barang ?? ('#'.$detail->inventaris_id),
                    $jumlah,
                    (int) $detail->inventaris?->stok,
                );
            }
        }
    }
}
