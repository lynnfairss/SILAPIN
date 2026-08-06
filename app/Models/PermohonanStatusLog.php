<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermohonanStatusLog extends Model
{
    protected $fillable = [
        'permohonan_id',
        'status_lama',
        'status_baru',
        'catatan',
        'user_id',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
