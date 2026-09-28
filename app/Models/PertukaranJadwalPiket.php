<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class PertukaranJadwalPiket extends Model
{
    protected $table = 'pertukaran_jadwal_piket';

    protected $fillable = [
        'jadwal_piket_id',
        'guru_asal_id',
        'guru_pengganti_id',
        'tanggal',
        'alasan',
        'status',
        'is_active',
        'diminta_at',
        'diproses_at',
        'diproses_oleh',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'is_active' => 'boolean',
            'diminta_at' => 'datetime',
            'diproses_at' => 'datetime',
        ];
    }

    public function jadwalPiket(): BelongsTo
    {
        return $this->belongsTo(JadwalPiket::class, 'jadwal_piket_id');
    }

    public function guruAsal(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_asal_id');
    }

    public function guruPengganti(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_pengganti_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    /**
     * Memvalidasi bahwa guru asal merupakan anggota resmi jadwal.
     */
    public static function validateGuruAsal(JadwalPiket $jadwal, int $guruAsalId): bool
    {
        if (! $jadwal->isAnggotaJadwal($guruAsalId)) {
            throw new InvalidArgumentException("Guru asal (ID: {$guruAsalId}) bukan merupakan anggota resmi pada jadwal ini.");
        }

        return true;
    }

    /**
     * Memvalidasi bahwa guru pengganti aktif (status_aktif = true).
     * Guru pengganti tidak harus memiliki jadwal.
     */
    public static function validateGuruPengganti(int $guruPenggantiId): bool
    {
        $guru = Guru::find($guruPenggantiId);
        if (! $guru || ! $guru->status_aktif) {
            throw new InvalidArgumentException("Guru pengganti (ID: {$guruPenggantiId}) harus berstatus aktif.");
        }

        return true;
    }

    /**
     * Mengaktifkan replacement ini secara operasional dan menonaktifkan replacement aktif
     * lainnya untuk jadwal, guru asal, dan tanggal yang sama (menjaga maksimal 1 active replacement).
     */
    public function activate(): void
    {
        // Nonaktifkan replacement aktif lain untuk slot yang sama
        self::where('jadwal_piket_id', $this->jadwal_piket_id)
            ->where('guru_asal_id', $this->guru_asal_id)
            ->whereDate('tanggal', $this->tanggal)
            ->where('id', '!=', $this->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }
}
