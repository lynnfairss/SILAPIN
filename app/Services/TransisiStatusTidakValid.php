<?php

namespace App\Services;

use RuntimeException;

class TransisiStatusTidakValid extends RuntimeException
{
    public static function dari(string $dari, string $ke): self
    {
        $tujuan = TransisiStatus::tujuan($dari);

        $pesan = empty($tujuan)
            ? sprintf('Status "%s" sudah final dan tidak dapat diubah lagi.', $dari)
            : sprintf('Status "%s" tidak dapat diubah menjadi "%s".', $dari, $ke);

        return new self($pesan);
    }
}
