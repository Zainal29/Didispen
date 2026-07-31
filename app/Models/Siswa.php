<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['user_id', 'jurusan_id', 'kelas_id', 'nama_lengkap', 'tanggal_lahir', 'alamat', 'no_telepon'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
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
