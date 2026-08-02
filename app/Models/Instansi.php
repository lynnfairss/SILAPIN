<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $fillable = [
        'nama_instansi',
        'alamat',
        'telepon',
        'tipe_identitas',
    ];

    private static $keywordMap = [
        'polres'    => 'NRP', 'polsek'   => 'NRP', 'polresta'  => 'NRP',
        'poltabes'  => 'NRP', 'polda'    => 'NRP',
        'kodim'     => 'NDP/NRP', 'korem'  => 'NDP/NRP', 'koramil' => 'NDP/NRP',
        'mabes'     => 'NDP/NRP', 'tni'    => 'NDP/NRP', 'denma'   => 'NDP/NRP',
        'dinas'     => 'NIP', 'pemkot'   => 'NIP', 'pemkab'   => 'NIP',
        'kecamatan' => 'NIP', 'sekretariat' => 'NIP', 'pemerintah' => 'NIP',
        'sma'       => 'NIP', 'smk'      => 'NIP', 'sdn'      => 'NIP',
        'smp'       => 'NIP', 'sd'       => 'NIP',
    ];

    public function getEffectiveTipeIdentitasAttribute(): string
    {
        if ($this->tipe_identitas && $this->tipe_identitas !== 'NIK') {
            return $this->tipe_identitas;
        }

        $lower = strtolower($this->nama_instansi ?? '');
        foreach (self::$keywordMap as $keyword => $tipe) {
            if (str_contains($lower, $keyword)) {
                return $tipe;
            }
        }

        return 'NIK';
    }

    public function permohonan()
    {
        return $this->hasMany(Permohonan::class);
    }
}