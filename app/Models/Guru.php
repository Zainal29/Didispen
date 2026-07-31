<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Guru extends Model
{
    protected $table = 'guru';
    protected $fillable = ['user_id', 'nip', 'nama_lengkap', 'mata_pelajaran', 'digital_signature', 'signature_base64_backup'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function piket(): HasMany
    {
        return $this->hasMany(GuruPiket::class, 'guru_id');
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->digital_signature ? Storage::url($this->digital_signature) : null;
    }

    public function piketHariIni(): ?GuruPiket
    {
        return $this->piket()
            ->where('tanggal', now()->toDateString())
            ->first();
    }
}
