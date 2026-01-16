<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(
        string $action,
        ?Model $auditable = null,
        ?User $user = null,
        ?Request $request = null,
        ?array $before = null,
        ?array $after = null
    ): void {
        $request = $request ?? request();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->id,
            'before' => $before,
            'after' => $after,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
