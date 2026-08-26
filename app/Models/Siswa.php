<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahkan ini
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory; // Tambahkan ini

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

    public function getNamaLengkapAttribute($value): string
    {
        return strtoupper($value);
    }
}
