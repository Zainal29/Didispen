<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use Prunable;

    protected $table = 'audit_logs';
    protected $fillable = ['user_id', 'action', 'table_name', 'record_id', 'old_value', 'new_value', 'ip_address'];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
        ];
    }

    /**
     * Audit log yang berumur lebih dari 24 jam
     * akan dihapus otomatis oleh command model:prune.
     */
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subHours(24));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}