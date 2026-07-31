<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $fillable = ['kode_jurusan', 'nama_jurusan'];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }
}
