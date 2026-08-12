<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuruPiket extends Model
{
    protected $table = 'guru_piket';
    
    protected $fillable = [
        'guru_id',
        'tanggal',
        'shift',
    ];

    // Casting agar 'tanggal' otomatis menjadi objek Carbon
    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function dispensasi(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'guru_piket_id');
    }
}