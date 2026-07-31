<?php
namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public function log(
        int $userId,
        string $action,
        string $tableName,
        ?int $recordId = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId ?? 0,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
        ]);
    }
}
