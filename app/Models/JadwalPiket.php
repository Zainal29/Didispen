<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use RuntimeException;

class JadwalPiket extends Model
{
    protected $table = 'jadwal_piket';

    protected $fillable = [
        'hari',
        'nama_sesi',
        'jam_mulai',
        'jam_selesai',
        'koordinator_guru_id',
        'tanggal_mulai_berlaku',
        'tanggal_selesai_berlaku',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hari' => 'integer',
            'koordinator_guru_id' => 'integer',
            'tanggal_mulai_berlaku' => 'date',
            'tanggal_selesai_berlaku' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function koordinator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Guru::class, 'koordinator_guru_id');
    }

    public function jadwalPiketGurus(): HasMany
    {
        return $this->hasMany(JadwalPiketGuru::class, 'jadwal_piket_id');
    }

    public function pertukaranJadwalPikets(): HasMany
    {
        return $this->hasMany(PertukaranJadwalPiket::class, 'jadwal_piket_id');
    }

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'jadwal_piket_guru', 'jadwal_piket_id', 'guru_id')
            ->withTimestamps();
    }

    /**
     * Memeriksa apakah guru merupakan anggota resmi pada jadwal ini.
     */
    public function isAnggotaJadwal(int $guruId): bool
    {
        return $this->jadwalPiketGurus()->where('guru_id', $guruId)->exists();
    }

    /**
     * Menghitung petugas aktual pada tanggal tertentu berdasarkan jadwal resmi dan replacement aktif.
     * Mengembalikan collection Guru.
     *
     * @throws RuntimeException jika terjadi conflict (lebih dari satu replacement aktif untuk guru asal yang sama)
     */
    public function getPetugasAktual(string|Carbon $tanggal): Collection
    {
        $tanggalStr = $tanggal instanceof Carbon ? $tanggal->toDateString() : Carbon::parse($tanggal)->toDateString();

        // 1. Ambil guru resmi dari jadwal_piket_guru
        $guruResmi = $this->jadwalPiketGurus()->with('guru')->get()->pluck('guru')->filter();

        // 2. Ambil replacement aktif pada tanggal ini
        $activeReplacements = $this->pertukaranJadwalPikets()
            ->whereDate('tanggal', $tanggalStr)
            ->where('is_active', true)
            ->with(['guruAsal', 'guruPengganti'])
            ->get();

        // 3. Deteksi conflict: Jika ada guru_asal_id yang memiliki > 1 replacement aktif
        $grouped = $activeReplacements->groupBy('guru_asal_id');
        foreach ($grouped as $guruAsalId => $replacements) {
            if ($replacements->count() > 1) {
                throw new RuntimeException("Conflict: Terdapat lebih dari satu replacement aktif untuk guru asal ID {$guruAsalId} pada tanggal {$tanggalStr}.");
            }
        }

        // 4. Petugas aktual: Ganti guru resmi yang memiliki replacement aktif dengan guru pengganti
        $petugasAktual = new Collection();

        foreach ($guruResmi as $resmi) {
            $replacement = $activeReplacements->firstWhere('guru_asal_id', $resmi->id);
            if ($replacement && $replacement->guruPengganti) {
                $petugasAktual->push($replacement->guruPengganti);
            } else {
                $petugasAktual->push($resmi);
            }
        }

        return $petugasAktual;
    }
}
