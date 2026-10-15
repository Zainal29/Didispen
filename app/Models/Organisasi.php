<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisasi extends Model
{
    use HasFactory;

    protected $table = 'organisasi';

    protected $fillable = [
        'nama_organisasi',
        'singkatan',
        'kategori',
        'ketua_siswa_id',
        'pembina_guru_id',
        'deskripsi',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function ketua(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'ketua_siswa_id');
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'pembina_guru_id');
    }

    public function dispensasi(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'organisasi_id');
    }

    /**
     * Scope query organisasi aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }
}
