<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Dispensasi extends Model
{
    use HasFactory;

    protected $table = 'dispensasi';

    protected $fillable = [
        'siswa_id',
        'guru_id',              // ✅ Guru yang menyetujui/menolak (terisi saat approve)
        'nomor_surat',
        'kategori',
        'alasan',
        'tujuan',
        'lokasi',
        'jam_keluar',
        'jam_kembali',
        'batas_waktu_kembali',  // ✅ BARU
        'status',
        'catatan_admin',
        'qr_code',
        'qr_token',
        'bukti_file',
        'print_count',
        'max_print_limit',
        'printed_at',
        'waktu_keluar_aktual',
        'waktu_kembali_aktual',
        'satpam_keluar_id',
        'satpam_kembali_id',
        'is_warned',            // ✅ BARU
        'warned_at',            // ✅ BARU
        'foto_verifikasi',
    ];

    protected $casts = [
        'batas_waktu_kembali' => 'datetime',
        'is_warned' => 'boolean',
        'warned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Dispensasi $dispensasi) {
            $dispensasi->qr_token ??= Str::random(64);
        });
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * ✅ HELPER: Cek apakah dispensasi ini sudah overdue (terlambat)
     */
    public function isOverdue(): bool
    {
        if (! $this->batas_waktu_kembali) {
            return false;
        }

        return $this->status === 'keluar' &&
            now()->greaterThan($this->batas_waktu_kembali);
    }

    /**
     * ✅ HELPER: Tandai sebagai sudah diberi peringatan
     */
    public function markAsWarned(): void
    {
        $this->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);
    }

    public static function boot()
    {
        parent::boot();

        // Auto-delete foto saat dispensasi dihapus
        static::deleted(function ($dispensasi) {
            if ($dispensasi->foto_verifikasi && \Storage::disk('public')->exists($dispensasi->foto_verifikasi)) {
                \Storage::disk('public')->delete($dispensasi->foto_verifikasi);
            }
        });
    }
}
