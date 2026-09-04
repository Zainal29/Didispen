<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use Prunable;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_value',
        'new_value',
        'ip_address',
        'device_type',
        'os',
        'browser',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    /**
     * Hapus audit log yang lebih dari 24 jam.
     */
    public function prunable()
    {
        return static::where(
            'created_at',
            '<=',
            now()->subHours(24)
        );
    }

    /**
     * Relasi ke user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
