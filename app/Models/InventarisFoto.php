<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarisFoto extends Model
{
    protected $table = 'inventaris_fotos';

    protected $fillable = [
        'inventaris_id',
        'foto',
        'urutan',
    ];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}
