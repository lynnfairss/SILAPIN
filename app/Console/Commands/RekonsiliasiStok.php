<?php

namespace App\Console\Commands;

use App\Models\DetailPermohonan;
use App\Models\Inventaris;
use App\Services\TransisiStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Memperbaiki stok yang meleset pada data lama.
 *
 * Sebelum service stok diperbaiki, inventaris.stok hanya pernah bertambah
 * (saat pengembalian) dan tidak pernah berkurang (saat persetujuan). Akibatnya
 * setiap barang yang pernah dipinjam dan dikembalikan tercatat satu kali
 *lipatan, dan barang yang masih dipinjam tidak pernah tercermin sebagai
 * berkurang.
 *
 * Karena tidak ada satu pun pengurangan yang pernah tercatat, keduanya harus
 * dikoreksi dari nol:
 *
 *     stok_koreksi = stok_sekarang - SUM(jumlah pada permohonan dengan status
 *                    Disetujui / Dipinjam / Dikembalikan)
 *
 * Perintah ini hanya menampilkan hitungan (dry-run) kecuali dijalankan dengan
 * --apply.
 */
class RekonsiliasiStok extends Command
{
    protected $signature = 'stok:rekonsiliasi
        {--apply : Tulis koreksi ke database. Tanpa flag ini hanya menampilkan hitungan.}';

    protected $description = 'Mengoreksi inventaris.stok agar kembali sesuai dengan peminjaman yang tercatat.';

    public function handle(): int
    {
        $sedangDipakai = [
            TransisiStatus::DISETUJUI,
            TransisiStatus::DIPINJAM,
            TransisiStatus::DIKEMBALIKAN,
        ];

        $terkunci = DetailPermohonan::query()
            ->join('permohonans', 'permohonans.id', '=', 'detail_permohonans.permohonan_id')
            ->whereIn('permohonans.status', $sedangDipakai)
            ->groupBy('detail_permohonans.inventaris_id')
            ->selectRaw('detail_permohonans.inventaris_id, SUM(detail_permohonans.jumlah) as total')
            ->pluck('total', 'detail_permohonans.inventaris_id');

        $baris = [];

        foreach (Inventaris::orderBy('kode_barang')->get() as $barang) {
            $stokSekarang = (int) $barang->stok;
            $pernahDipinjam = (int) ($terkunci[$barang->id] ?? 0);
            $koreksi = $stokSekarang - $pernahDipinjam;

            $baris[] = [$barang, $stokSekarang, $pernahDipinjam, $koreksi];
        }

        if ($baris === []) {
            $this->warn('Tidak ada data inventaris.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->table(
            ['Kode', 'Nama Barang', 'Stok Sekarang', 'Pernah Dipinjam', 'Stok Sebenarnya', 'Selisih'],
            array_map(fn ($b) => [
                $b[0]->kode_barang,
                Str::limit((string) $b[0]->nama_barang, 28),
                $b[1],
                $b[2],
                $b[3],
                $b[1] - $b[3],
            ], $baris),
        );

        $berubah = array_values(array_filter($baris, fn ($b) => $b[1] !== $b[3]));
        $negatif = array_values(array_filter($berubah, fn ($b) => $b[3] < 0));

        $this->components->twoColumnDetail('Barang perlu dikoreksi', (string) count($berubah));
        $this->components->twoColumnDetail('Hasil negatif', (string) count($negatif));

        if ($negatif !== []) {
            $this->newLine();
            $this->components->error(
                'Ada barang yang hasil koreksinya negatif. Artinya data peminjaman tidak cocok dengan riwayat stok. '
                .'Periksa dulu secara manual sebelum memakai --apply.'
            );
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->components->info('Mode dry-run. Tidak ada data yang diubah.');

            if ($berubah !== []) {
                $this->line(' Jalankan <fg=green>php artisan stok:rekonsiliasi --apply</> untuk menyimpan koreksi.');
            }

            return $negatif === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($negatif !== []) {
            $this->components->error('Dibatalkan karena ada hasil negatif.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($berubah) {
            foreach ($berubah as [$barang, $stokSekarang, , $koreksi]) {
                Inventaris::whereKey($barang->id)->update(['stok' => $koreksi]);

                $this->line(sprintf(
                    '  <fg=gray>%s</> stok %d -> %d',
                    $barang->kode_barang,
                    $stokSekarang,
                    $koreksi,
                ));
            }
        });

        $this->newLine();
        $this->components->info(count($berubah).' barang berhasil dikoreksi.');

        return self::SUCCESS;
    }
}
