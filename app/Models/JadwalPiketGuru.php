<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPiketGuru extends Model
{
    protected $table = 'jadwal_piket_guru';

    protected $fillable = [
        'jadwal_piket_id',
        'guru_id',
    ];

    public function jadwalPiket(): BelongsTo
    {
        return $this->belongsTo(JadwalPiket::class, 'jadwal_piket_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}
