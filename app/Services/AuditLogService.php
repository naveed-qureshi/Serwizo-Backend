<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function log(
        string $action,
        string $module,
        string|int|null $recordId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): AuditLog {
        $authUser = Auth::user();
        $finalUserId = $userId;
        if ($finalUserId === null && $authUser instanceof \App\Models\User) {
            $finalUserId = $authUser->id;
        } elseif ($finalUserId !== null && !\App\Models\User::where('id', $finalUserId)->exists()) {
            $finalUserId = null;
        }

        return AuditLog::create([
            'user_id' => $finalUserId,
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId ? (string)$recordId : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::header('User-Agent'),
            'created_at' => now(),
        ]);
    }
}
