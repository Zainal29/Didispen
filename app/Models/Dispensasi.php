<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage; // ✅ TAMBAHKAN INI
use Illuminate\Support\Str;

class Dispensasi extends Model
{
    use SoftDeletes;

    protected $table = 'dispensasi';

    protected $fillable = [
        'siswa_id', 'guru_id', 'nomor_surat', 'tipe_pengajuan', 'organisasi_id', 'nama_organisasi',
        'kategori', 'alasan', 'tujuan', 'lokasi', 'tanggal_izin', 'tanggal_selesai', 'durasi_hari',
        'jam_keluar', 'jam_kembali', 'sampai_pulang', 'batas_waktu_kembali', 'status', 'catatan_admin',
        'approved_at', 'rejected_at',
        'qr_code', 'qr_token', 'print_count', 'max_print_limit', 'printed_at',
        'student_print_count', 'teacher_print_count', 'waktu_keluar_aktual', 'waktu_kembali_aktual',
        'satpam_keluar_id', 'satpam_kembali_id', 'is_warned', 'warned_at',
        'dibuat_manual_oleh_guru',
        'foto_verifikasi', 'foto_bukti', 'foto_bukti_uploaded_at', // ✅ Hapus 'bukti_file'
    ];


    protected $casts = [
        'tanggal_izin' => 'date',
        'tanggal_selesai' => 'date',
        'durasi_hari' => 'integer',
        'sampai_pulang' => 'boolean',
        'batas_waktu_kembali' => 'datetime',
        'waktu_keluar_aktual' => 'datetime',
        'waktu_kembali_aktual' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_warned' => 'boolean',
        'dibuat_manual_oleh_guru' => 'boolean',
        'warned_at' => 'datetime',
        'foto_bukti_uploaded_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Dispensasi $dispensasi) {
            $dispensasi->qr_token ??= Str::random(64);
        });

        static::deleted(function (Dispensasi $dispensasi) {
            // Hanya hapus file jika dihapus secara permanen (force delete)
            if (method_exists($dispensasi, 'isForceDeleting') && ! $dispensasi->isForceDeleting()) {
                return;
            }

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

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class, 'organisasi_id');
    }

    /**
     * Anggota rombongan untuk dispensasi organisasi
     */
    public function anggotaSiswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'dispensasi_anggota', 'dispensasi_id', 'siswa_id')->withTimestamps();
    }

    /**
     * Guru Pembina Ekstra / Organisasi yang dipilih untuk dispensasi rombongan
     */
    public function gurus(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'dispensasi_guru', 'dispensasi_id', 'guru_id')->withTimestamps();
    }

    /**
     * Cek apakah tipe pengajuan adalah organisasi / rombongan
     */
    public function isOrganisasi(): bool
    {
        return $this->tipe_pengajuan === 'organisasi';
    }

    /**
     * Cek apakah tipe pengajuan adalah individu / mandiri
     */
    public function isIndividu(): bool
    {
        return $this->tipe_pengajuan === 'individu' || empty($this->tipe_pengajuan);
    }

    /**
     * Cek apakah dispensasi berstatus izin sampai jam pulang sekolah
     */
    public function isSampaiPulang(): bool
    {
        return (bool) $this->sampai_pulang;
    }

    /**
     * Cek apakah saat ini sedang keluar dan melewati batas waktu (Overdue)
     */
    public function isOverdue(): bool
    {
        if ($this->sampai_pulang) {
            return false;
        }

        return $this->status === 'keluar'
            && $this->batas_waktu_kembali !== null
            && now()->greaterThan($this->batas_waktu_kembali);
    }

    /**
     * Cek apakah dispensasi berstatus 'selesai' namun kembali melewati batas waktu
     */
    public function isReturnedLate(): bool
    {
        if ($this->sampai_pulang) {
            return false;
        }

        return $this->status === 'selesai'
            && $this->batas_waktu_kembali !== null
            && $this->waktu_kembali_aktual !== null
            && $this->waktu_kembali_aktual->greaterThan($this->batas_waktu_kembali);
    }

    /**
     * Cek apakah dispensasi berstatus 'selesai' tetapi tidak pernah melakukan scan kembali (auto-closed)
     */
    public function isNotReturned(): bool
    {
        return $this->status === 'selesai'
            && ! $this->sampai_pulang
            && $this->waktu_keluar_aktual !== null
            && $this->waktu_kembali_aktual === null;
    }

    /**
     * Hitung durasi keterlambatan dalam menit (baik sedang keluar maupun sudah selesai)
     */
    public function getLateMinutes(): int
    {
        if ($this->isOverdue()) {
            return \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($this->batas_waktu_kembali, now());
        }

        if ($this->isReturnedLate()) {
            return \App\Helpers\DispensasiTimeHelper::hitungMenitTerlambat($this->batas_waktu_kembali, $this->waktu_kembali_aktual);
        }

        return 0;
    }

    /**
     * Format teks keterlambatan ramah pengguna (contoh: "15 menit" atau "1 jam 20 mnt")
     */
    public function getLateDurationText(bool $short = false): string
    {
        $minutes = $this->getLateMinutes();
        if ($minutes <= 0) {
            return '';
        }

        return \App\Helpers\DispensasiTimeHelper::formatDurasiTerlambat($minutes, $short);
    }

    /**
     * Mendapatkan metadata status visual seragam untuk seluruh portal
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->status === 'keluar') {
            if ($this->sampai_pulang) {
                return [
                    'text' => 'Keluar (Sampai Pulang)',
                    'class' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                    'dot' => 'bg-indigo-500',
                    'is_late' => false,
                ];
            }

            if ($this->isOverdue()) {
                $duration = $this->getLateDurationText(true);
                return [
                    'text' => 'Terlambat' . ($duration ? ' ' . $duration : ''),
                    'class' => 'bg-rose-100 text-rose-800 border-rose-200',
                    'dot' => 'bg-rose-500',
                    'is_late' => true,
                ];
            }
        }

        if ($this->status === 'selesai') {
            if ($this->sampai_pulang) {
                return [
                    'text' => 'Selesai (Sampai Pulang)',
                    'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'dot' => 'bg-emerald-500',
                    'is_late' => false,
                ];
            }

            if ($this->isNotReturned()) {
                return [
                    'text' => 'Selesai (Tidak Kembali)',
                    'class' => 'bg-red-100 text-red-800 border-red-200',
                    'dot' => 'bg-red-500',
                    'is_late' => true,
                ];
            }

            if ($this->isReturnedLate()) {
                $duration = $this->getLateDurationText(true);
                return [
                    'text' => 'Selesai (Terlambat' . ($duration ? ' ' . $duration : '') . ')',
                    'class' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'dot' => 'bg-amber-500',
                    'is_late' => true,
                ];
            }

            return [
                'text' => 'Selesai (Tepat Waktu)',
                'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'is_late' => false,
            ];
        }

        return match ($this->status) {
            'menunggu' => [
                'text' => 'Menunggu',
                'class' => 'bg-amber-100 text-amber-800 border-amber-200',
                'dot' => 'bg-amber-500',
                'is_late' => false,
            ],
            'disetujui' => [
                'text' => 'Disetujui',
                'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'is_late' => false,
            ],
            'keluar' => [
                'text' => 'Sedang Keluar',
                'class' => 'bg-sky-100 text-sky-800 border-sky-200',
                'dot' => 'bg-sky-500',
                'is_late' => false,
            ],
            'ditolak' => [
                'text' => 'Ditolak',
                'class' => 'bg-red-100 text-red-800 border-red-200',
                'dot' => 'bg-red-500',
                'is_late' => false,
            ],
            'dibatalkan' => [
                'text' => 'Dibatalkan',
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'dot' => 'bg-gray-400',
                'is_late' => false,
            ],
            'kadaluarsa' => [
                'text' => ($this->waktu_keluar_aktual && empty($this->waktu_kembali_aktual))
                    ? 'Kadaluarsa (Tidak Kembali)'
                    : (($this->catatan_admin && str_contains($this->catatan_admin, 'scan keluar'))
                        ? 'Kadaluarsa (Tidak Scan Keluar)'
                        : 'Kadaluarsa'),
                'class' => ($this->waktu_keluar_aktual && empty($this->waktu_kembali_aktual))
                    ? 'bg-rose-100 text-rose-800 border-rose-200'
                    : 'bg-orange-100 text-orange-800 border-orange-200',
                'dot' => ($this->waktu_keluar_aktual && empty($this->waktu_kembali_aktual))
                    ? 'bg-rose-500'
                    : 'bg-orange-500',
                'is_late' => (bool) ($this->waktu_keluar_aktual && empty($this->waktu_kembali_aktual)),
            ],
            default => [
                'text' => ucfirst($this->status),
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'dot' => 'bg-gray-400',
                'is_late' => false,
            ],
        };
    }

    /**
     * Waktu berakhir KBM pada hari dispensasi (format HH:mm WIB)
     */
    public function getJamSelesaiKbmText(): string
    {
        $date = $this->tanggal_izin
            ? \Carbon\Carbon::parse($this->tanggal_izin, 'Asia/Jakarta')
            : ($this->created_at ? $this->created_at->copy()->setTimezone('Asia/Jakarta') : now('Asia/Jakarta'));

        $selesai = \App\Helpers\TimeHelper::getWaktuSelesaiKbmTerakhir($date->dayOfWeek, $date);
        return $selesai ? $selesai->format('H:i') . ' WIB' : '15:15 WIB';
    }

    /**
     * Cek apakah pengajuan status 'menunggu' berisiko kadaluarsa hari ini
     */
    public function isMenungguHariIni(): bool
    {
        if ($this->status !== 'menunggu') {
            return false;
        }

        $date = $this->tanggal_izin
            ? \Carbon\Carbon::parse($this->tanggal_izin, 'Asia/Jakarta')
            : ($this->created_at ? $this->created_at->copy()->setTimezone('Asia/Jakarta') : now('Asia/Jakarta'));

        return $date->toDateString() === now('Asia/Jakarta')->toDateString();
    }

    /**
     * Tandai sebagai sudah diberi peringatan
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
