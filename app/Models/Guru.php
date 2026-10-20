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
        'email',
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

    public function jadwalPiketGurus(): HasMany
    {
        return $this->hasMany(JadwalPiketGuru::class, 'guru_id');
    }

    public function pertukaranAsal(): HasMany
    {
        return $this->hasMany(PertukaranJadwalPiket::class, 'guru_asal_id');
    }

    public function pertukaranPengganti(): HasMany
    {
        return $this->hasMany(PertukaranJadwalPiket::class, 'guru_pengganti_id');
    }

    public function jadwalPikets(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(JadwalPiket::class, 'jadwal_piket_guru', 'guru_id', 'jadwal_piket_id')
            ->withTimestamps();
    }
}
