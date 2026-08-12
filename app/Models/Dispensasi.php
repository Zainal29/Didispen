<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahkan ini
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispensasi extends Model
{
    use HasFactory; // Tambahkan ini

    // ✅ INI SUDAH BENAR, mencegah error 'dispensasis'
    protected $table = 'dispensasi';

    protected $fillable = [
        'siswa_id',
        'guru_piket_id',
        'nomor_surat',
        'kategori',
        'alasan',
        'tujuan',
        'lokasi',
        'jam_keluar',
        'jam_kembali',
        'status',
        'catatan_admin',
        'qr_code',
        'bukti_file',
        'print_count',
        'max_print_limit',
        'printed_at',
        'waktu_keluar_aktual',
        'waktu_kembali_aktual',
        'satpam_keluar_id',
        'satpam_kembali_id',
    ];

    // PASTIKAN TIDAK ADA $casts untuk jam_keluar/jam_kembali di sini

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guruPiket()
    {
        return $this->belongsTo(GuruPiket::class);
    }
}