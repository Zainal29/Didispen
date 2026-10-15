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

    public function dispensasiOrganisasi(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Dispensasi::class, 'dispensasi_anggota', 'siswa_id', 'dispensasi_id')->withTimestamps();
    }

    public function organisasiDipimpin(): HasMany
    {
        return $this->hasMany(Organisasi::class, 'ketua_siswa_id');
    }

    public function isKetuaOrganisasi(): bool
    {
        return $this->organisasiDipimpin()->where('status_aktif', true)->exists();
    }

    public function getDaftarOrganisasiDipimpinAttribute()
    {
        return $this->organisasiDipimpin()->where('status_aktif', true)->get();
    }

    public function getNamaLengkapAttribute($value): string
    {
        // ✅ CASTING STRING AGAR AMAN DARI NILAI NULL
        return strtoupper((string) ($value ?? ''));
    }
}
