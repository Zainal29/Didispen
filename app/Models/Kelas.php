<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahkan baris ini
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory; // Tambahkan baris ini di dalam class

    protected $table = 'kelas';
    protected $fillable = ['jurusan_id', 'nama_kelas', 'tingkat'];

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}