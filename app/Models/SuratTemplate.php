<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratTemplate extends Model
{
    protected $fillable = [
        'template',
        'logo_kiri',
        'logo_kanan',
    ];

    protected $casts = [
        'template' => 'array',
    ];
}
