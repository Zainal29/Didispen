<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispensasi extends Model
{
    // ✅ INI SUDAH BENAR, mencegah error 'dispensasis'
    protected $table = 'dispensasi';

    protected $fillable = [
        'siswa_id',
        'guru_piket_id',
        'kategori',
        'alasan',
        'tujuan',
        'lokasi',
        'jam_keluar',
        'jam_kembali',
        'status',
        'catatan_admin',
        'nomor_surat',
        'bukti_file',       // ✅ Ditambahkan
        'print_count',
        'max_print_limit',  // ✅ Ditambahkan
    ];

    // PASTIKAN TIDAK ADA $casts untuk jam_keluar/jam_kembali di sini

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guruPiket(): BelongsTo
    {
        return $this->belongsTo(GuruPiket::class);
    }
}