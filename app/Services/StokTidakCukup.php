<?php

namespace App\Services;

use RuntimeException;

class StokTidakCukup extends RuntimeException
{
    public static function untuk(string $namaBarang, int $diminta, int $tersedia): self
    {
        return new self(sprintf(
            'Stok "%s" tidak mencukupi: %d diminta, %d tersedia. Perbarui stok atau ubah jumlah barang.',
            $namaBarang,
            $diminta,
            $tersedia,
        ));
    }
}
