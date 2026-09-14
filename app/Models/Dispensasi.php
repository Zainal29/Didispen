<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage; // ✅ TAMBAHKAN INI
use Illuminate\Support\Str;

class Dispensasi extends Model
{


    protected $table = 'dispensasi';

    protected $fillable = [
        'siswa_id', 'guru_id', 'nomor_surat', 'kategori', 'alasan', 'tujuan', 'lokasi',
        'jam_keluar', 'jam_kembali', 'batas_waktu_kembali', 'status', 'catatan_admin',
        'qr_code', 'qr_token', 'print_count', 'max_print_limit', 'printed_at',
        'student_print_count', 'teacher_print_count', 'waktu_keluar_aktual', 'waktu_kembali_aktual',
        'satpam_keluar_id', 'satpam_kembali_id', 'is_warned', 'warned_at',
        'foto_verifikasi', 'foto_bukti', 'foto_bukti_uploaded_at', // ✅ Hapus 'bukti_file'
    ];


    protected $casts = [
        'batas_waktu_kembali' => 'datetime',
        'is_warned' => 'boolean',
        'warned_at' => 'datetime',
        'foto_bukti_uploaded_at' => 'datetime', // <i class="fas fa-check-circle"></i> TAMBAHKAN INI
    ];

    protected static function booted(): void
    {
        static::creating(function (Dispensasi $dispensasi) {
            $dispensasi->qr_token ??= Str::random(64);
        });

        static::deleted(function (Dispensasi $dispensasi) {
            $files = array_filter([
                $dispensasi->qr_code,
                $dispensasi->foto_verifikasi,
                $dispensasi->foto_bukti,
            ]);

            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }
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
        * Relasi ke User yang menyetujui dispensasi
        */
       public function approvedBy(): BelongsTo
       {
           return $this->belongsTo(User::class, 'disetujui_oleh');
       }


    /**
     * <i class="fas fa-check-circle"></i> HELPER: Cek apakah dispensasi ini sudah overdue (terlambat)
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
     * <i class="fas fa-check-circle"></i> HELPER: Tandai sebagai sudah diberi peringatan
     */
    public function markAsWarned(): void
    {
        $this->update([
            'is_warned' => true,
            'warned_at' => now(),
        ]);
    }

    /**
     * Generate nomor surat dispensasi secara unik
     */
    public static function generateNomorSurat(): string
    {
        $tanggal = now()->format('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "DISP/{$tanggal}/{$random}";
    }

}
