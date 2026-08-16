<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class GuruPiket extends Model
{
    protected $table = 'guru_piket';

    protected $fillable = [
        'guru_id',
        'hari',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Scope: Ambil jadwal piket untuk hari ini
     */
    public function scopeHariIni(Builder $query): Builder
    {
        $mapHari = [
            'Monday'    => 'senin',
            'Tuesday'   => 'selasa',
            'Wednesday' => 'rabu',
            'Thursday'  => 'kamis',
            'Friday'    => 'jumat',
            'Saturday'  => 'sabtu',
            'Sunday'    => 'minggu',
        ];

        $hariInggris = now()->format('l'); // Monday, Tuesday, dst
        $hariIndonesia = $mapHari[$hariInggris] ?? null;

        return $query->where('hari', $hariIndonesia);
    }
}