<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'tanggal_lahir',
        'mata_pelajaran',
        'no_telepon',
        'alamat',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'status_aktif' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke semua dispensasi yang pernah diproses/disetujui oleh guru ini.
     */
    public function dispensasi(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'guru_id');
    }
}
