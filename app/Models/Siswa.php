<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahkan ini
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
     // Tambahkan ini

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nis_nip',
        'kelas_id',
        'jurusan_id',
        'nama_lengkap',
        'no_telepon',
        'alamat',
        'tanggal_lahir',
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

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dispensasi(): HasMany
    {
        return $this->hasMany(Dispensasi::class);
    }

    public function dispensasiRombongan(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Dispensasi::class, 'dispensasi_anggota', 'siswa_id', 'dispensasi_id')->withTimestamps();
    }

    public function dispensasiOrganisasi(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->dispensasiRombongan();
    }

    public function laporanSatpam(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\LaporanSatpam::class, 'siswa_id');
    }

    public function getNamaLengkapAttribute($value): string
    {
        // ✅ CASTING STRING AGAR AMAN DARI NILAI NULL
        return strtoupper((string) ($value ?? ''));
    }

    public function getNisAttribute(): ?string
    {
        return $this->nis_nip ?: ($this->user?->nis_nip ?: null);
    }

    public function getNisnAttribute(): ?string
    {
        return $this->nis_nip ?: ($this->user?->nis_nip ?: null);
    }
}
