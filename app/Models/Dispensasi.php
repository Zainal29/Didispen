<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Dispensasi extends Model
{
    use SoftDeletes;

    protected $table = 'dispensasi';

    protected $fillable = [
        'siswa_id', 'guru_id', 'nomor_surat', 'tipe_pengajuan',
        'kategori', 'alasan', 'tujuan', 'lokasi', 'tanggal_izin',
        'jam_keluar', 'jam_kembali', 'sampai_pulang', 'batas_waktu_kembali', 'status', 'catatan_admin',
        'approved_at', 'rejected_at',
        'qr_code', 'qr_token', 'print_count', 'max_print_limit', 'printed_at',
        'student_print_count', 'teacher_print_count', 'waktu_keluar_aktual', 'waktu_kembali_aktual',
        'satpam_keluar_id', 'satpam_kembali_id', 'is_warned', 'warned_at',
        'dibuat_manual_oleh_guru',
        'foto_verifikasi', 'foto_bukti', 'foto_bukti_uploaded_at',
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

    /**
     * Anggota rombongan untuk dispensasi rombongan
     */
    public function anggotaSiswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'dispensasi_anggota', 'dispensasi_id', 'siswa_id')->withTimestamps();
    }

    /**
     * Cek apakah tipe pengajuan adalah rombongan
     */
    public function isRombongan(): bool
    {
        return $this->tipe_pengajuan === 'rombongan' || $this->tipe_pengajuan === 'organisasi';
    }

    /**
     * Alias backward-compatible untuk isRombongan()
     */
    public function isOrganisasi(): bool
    {
        return $this->isRombongan();
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
     * Toleransi keterlambatan scan kembali di pos gerbang dalam menit (antrean / buffer)
     */
    public const TOLERANSI_KETERLAMBATAN_MENIT = 5;

    /**
     * Durasi batas waktu pengajuan berstatus 'menunggu' disetujui Guru Piket (20 menit sejak diajukan)
     */
    public const DURASI_KADALUARSA_MENUNGGU_MENIT = 20;

    /**
     * Durasi batas waktu dispensasi berstatus 'disetujui' untuk scan keluar di pos gerbang (20 menit setelah disetujui)
     */
    public const DURASI_KADALUARSA_SCAN_KELUAR_MENIT = 20;

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
     * Cek apakah dispensasi berstatus 'selesai' dan kembali lebih awal sebelum batas jam yang ditentukan
     */
    public function isReturnedEarly(): bool
    {
        if ($this->sampai_pulang) {
            return false;
        }

        return $this->status === 'selesai'
            && $this->batas_waktu_kembali !== null
            && $this->waktu_kembali_aktual !== null
            && $this->waktu_kembali_aktual->lessThan($this->batas_waktu_kembali);
    }

    /**
     * Cek apakah dispensasi berstatus 'selesai' namun kembali melewati batas waktu + toleransi
     */
    public function isReturnedLate(): bool
    {
        if ($this->sampai_pulang) {
            return false;
        }

        return $this->status === 'selesai'
            && $this->batas_waktu_kembali !== null
            && $this->waktu_kembali_aktual !== null
            && $this->waktu_kembali_aktual->greaterThan(
                $this->batas_waktu_kembali->copy()->addMinutes(self::TOLERANSI_KETERLAMBATAN_MENIT)
            );
    }

    /**
     * Cek apakah dispensasi berstatus 'kadaluarsa' atau 'selesai' tetapi tidak pernah melakukan scan kembali
     */
    public function isNotReturned(): bool
    {
        return in_array($this->status, ['kadaluarsa', 'selesai'])
            && ! $this->sampai_pulang
            && $this->waktu_keluar_aktual !== null
            && $this->waktu_kembali_aktual === null;
    }

    /**
     * Cek apakah dispensasi ini termasuk kategori terlambat (selesai terlambat, overdue, atau tidak scan kembali)
     */
    public function isTerlambat(): bool
    {
        return $this->isOverdue() || $this->isReturnedLate() || $this->isNotReturned();
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
                    'text' => 'Kadaluarsa (Tidak Scan Kembali)',
                    'class' => 'bg-rose-50 text-rose-700 border-2 border-rose-500 font-bold shadow-xs',
                    'dot' => 'bg-rose-600',
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

            if ($this->isReturnedEarly()) {
                return [
                    'text' => 'Selesai (Kembali Lebih Awal)',
                    'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'dot' => 'bg-emerald-500',
                    'is_late' => false,
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
                    ? 'Kadaluarsa (Tidak Scan Kembali)'
                    : (($this->catatan_admin && (str_contains($this->catatan_admin, 'scan keluar') || str_contains($this->catatan_admin, 'check-out')))
                        ? 'Kadaluarsa (Tidak Scan Keluar)'
                        : 'Kadaluarsa (Tidak Minta ACC)'),
                'class' => ($this->waktu_keluar_aktual && empty($this->waktu_kembali_aktual))
                    ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 font-bold shadow-xs'
                    : (($this->catatan_admin && (str_contains($this->catatan_admin, 'scan keluar') || str_contains($this->catatan_admin, 'check-out')))
                        ? 'bg-orange-50 text-orange-800 border border-orange-300 font-semibold'
                        : 'bg-amber-50 text-amber-800 border border-amber-300 font-semibold'),
                'dot' => ($this->waktu_keluar_aktual && empty($this->waktu_kembali_aktual))
                    ? 'bg-rose-600'
                    : (($this->catatan_admin && (str_contains($this->catatan_admin, 'scan keluar') || str_contains($this->catatan_admin, 'check-out')))
                        ? 'bg-orange-500'
                        : 'bg-amber-500'),
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
     * Batas waktu toleransi untuk scan keluar di pos gerbang setelah disetujui (maksimal 20 menit)
     */
    public function getBatasWaktuScanKeluar(): ?\Carbon\Carbon
    {
        if ($this->status !== 'disetujui' && empty($this->approved_at)) {
            return null;
        }

        $dispDate = $this->tanggal_izin
            ? \Carbon\Carbon::parse($this->tanggal_izin, 'Asia/Jakarta')
            : ($this->created_at ? $this->created_at->copy()->setTimezone('Asia/Jakarta') : now('Asia/Jakarta'));

        $approvedAt = $this->approved_at
            ? $this->approved_at->copy()->setTimezone('Asia/Jakarta')
            : ($this->updated_at ? $this->updated_at->copy()->setTimezone('Asia/Jakarta') : $dispDate);

        // Titik awal jendela scan keluar:
        // Jika izin dijadwalkan pada jam tertentu di hari ini (misal jam ke-4: 09:15),
        // jendela scan keluar dimulai saat jam_keluar atau approved_at, mana yang lebih lambat
        $jamMulaiKeluar = \App\Helpers\TimeHelper::getWaktuMulaiKeluar($this->jam_keluar, $dispDate->dayOfWeek, $dispDate);
        $startWindow = $approvedAt->copy();
        if ($jamMulaiKeluar && $jamMulaiKeluar->greaterThan($startWindow)) {
            $startWindow = $jamMulaiKeluar->copy();
        }

        $batas20Menit = $startWindow->copy()->addMinutes(self::DURASI_KADALUARSA_SCAN_KELUAR_MENIT);

        // Batas atas maksimal adalah batas_waktu_kembali (jika bukan sampai pulang) atau jam selesai KBM
        $maxDeadline = null;
        if (! $this->sampai_pulang) {
            $maxDeadline = $this->batas_waktu_kembali;
            if (! $maxDeadline && $this->jam_kembali) {
                $maxDeadline = \App\Helpers\TimeHelper::getBatasWaktuKembali($this->jam_kembali, $dispDate->dayOfWeek);
            }
            if ($maxDeadline) {
                $maxDeadline = $maxDeadline->copy()->setTimezone('Asia/Jakarta')
                    ->setDate($dispDate->year, $dispDate->month, $dispDate->day);
            }
        }

        if (! $maxDeadline) {
            $maxDeadline = \App\Helpers\TimeHelper::getWaktuSelesaiKbmTerakhir($dispDate->dayOfWeek, $dispDate);
        }

        if ($maxDeadline && $batas20Menit->greaterThan($maxDeadline)) {
            return $maxDeadline;
        }

        return $batas20Menit;
    }

    /**
     * Batas waktu kadaluarsa efektif untuk dispensasi sebelum scan keluar (format HH:mm WIB)
     */
    public function getBatasWaktuExpiredText(): string
    {
        if ($this->status === 'menunggu') {
            $createdAt = $this->created_at
                ? $this->created_at->copy()->setTimezone('Asia/Jakarta')
                : now('Asia/Jakarta');
            return $createdAt->addMinutes(self::DURASI_KADALUARSA_MENUNGGU_MENIT)->format('H:i') . ' WIB';
        }

        if ($this->status === 'disetujui' && empty($this->waktu_keluar_aktual)) {
            $batasScanKeluar = $this->getBatasWaktuScanKeluar();
            if ($batasScanKeluar) {
                return $batasScanKeluar->format('H:i') . ' WIB';
            }
        }

        if (! $this->sampai_pulang) {
            $deadline = $this->batas_waktu_kembali;
            if (! $deadline && $this->jam_kembali) {
                $dispDate = $this->tanggal_izin
                    ? \Carbon\Carbon::parse($this->tanggal_izin, 'Asia/Jakarta')
                    : ($this->created_at ? $this->created_at->copy()->setTimezone('Asia/Jakarta') : now('Asia/Jakarta'));
                $deadline = \App\Helpers\TimeHelper::getBatasWaktuKembali($this->jam_kembali, $dispDate->dayOfWeek);
            }
            if ($deadline) {
                return $deadline->format('H:i') . ' WIB';
            }
        }

        return $this->getJamSelesaiKbmText();
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
     * Scope untuk menyaring dispensasi yang terlambat (selesai terlambat, overdue, atau tidak scan kembali)
     */
    public function scopeTerlambat(Builder $query): Builder
    {
        $toleransi = self::TOLERANSI_KETERLAMBATAN_MENIT;
        $driver = $query->getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            $rawLateCondition = "datetime(waktu_kembali_aktual) > datetime(batas_waktu_kembali, '+{$toleransi} minutes')";
        } else {
            $rawLateCondition = "waktu_kembali_aktual > DATE_ADD(batas_waktu_kembali, INTERVAL {$toleransi} MINUTE)";
        }

        return $query->where(function (Builder $q) use ($rawLateCondition) {
            // 1. Selesai Terlambat: Selesai, bukan sampai pulang, dan waktu_kembali_aktual > batas_waktu_kembali + toleransi
            $q->where(function (Builder $sub) use ($rawLateCondition) {
                $sub->where('status', 'selesai')
                    ->where(function ($sp) {
                        $sp->where('sampai_pulang', false)
                           ->orWhereNull('sampai_pulang');
                    })
                    ->whereNotNull('batas_waktu_kembali')
                    ->whereNotNull('waktu_kembali_aktual')
                    ->whereRaw($rawLateCondition);
            })
            // 2. Sedang Keluar & Melewati Batas Waktu Kembali (Overdue)
            ->orWhere(function (Builder $sub) {
                $sub->where('status', 'keluar')
                    ->where(function ($sp) {
                        $sp->where('sampai_pulang', false)
                           ->orWhereNull('sampai_pulang');
                    })
                    ->whereNotNull('batas_waktu_kembali')
                    ->where('batas_waktu_kembali', '<', now('Asia/Jakarta'));
            })
            // 3. Kadaluarsa / Selesai tapi tidak pernah scan kembali
            ->orWhere(function (Builder $sub) {
                $sub->whereIn('status', ['kadaluarsa', 'selesai'])
                    ->where(function ($sp) {
                        $sp->where('sampai_pulang', false)
                           ->orWhereNull('sampai_pulang');
                    })
                    ->whereNotNull('waktu_keluar_aktual')
                    ->whereNull('waktu_kembali_aktual');
            });
        });
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
