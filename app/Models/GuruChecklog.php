<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuruChecklog extends Model
{
    protected $table = 'guru_checklog';
    
    protected $fillable = [
        'guru_id', 'alasan', 'tujuan', 'lokasi', 
        'jam_keluar', 'jam_kembali', 'status'
    ];

    protected function casts(): array
    {
        return [
            'jam_keluar' => 'datetime',
            'jam_kembali' => 'datetime',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class)->with('user');
    }
}