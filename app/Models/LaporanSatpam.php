<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanSatpam extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'laporan_satpam';

    protected $fillable = [
        'satpam_id',
        'siswa_id',
        'dispensasi_id',
        'kategori',
        'keterangan',
        'status',
        'catatan_tindak_lanjut',
        'tindak_lanjut_oleh',
        'tindak_lanjut_at',
    ];

    protected $casts = [
        'tindak_lanjut_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const KATEGORI_LABELS = [
        'rombongan_tidak_kembali' => 'Dispensasi Rombongan: Siswa Tidak Kembali (Lapor Wali Kelas)',
        'terobos_gerbang'        => 'Keluar Tanpa Izin / Terobos Gerbang',
        'terlambat_kembali'      => 'Terlambat Kembali Tidak Wajar',
        'pelanggaran_lain'       => 'Pelanggaran / Kejadian di Gerbang',
    ];

    public const STATUS_LABELS = [
        'menunggu'        => 'Menunggu Tindakan Wali Kelas',
        'ditindaklanjuti' => 'Sedang Ditindaklanjuti Wali Kelas',
        'selesai'         => 'Selesai / Dikonfirmasi Wali Kelas',
    ];

    public function satpam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'satpam_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function dispensasi(): BelongsTo
    {
        return $this->belongsTo(Dispensasi::class, 'dispensasi_id');
    }

    public function petugasTindakLanjut(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tindak_lanjut_oleh');
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI_LABELS[$this->kategori] ?? ucfirst(str_replace('_', ' ', $this->kategori));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }
}
