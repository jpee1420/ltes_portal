<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an audit log entry.
     *
     * @param  string  $action  Action name e.g. 'user.created', 'auth.login'
     * @param  string|null  $description  Human readable event description
     * @param  Model|null  $subject  Optional model subject
     * @param  int|null  $userId  Explicit user id override (if auth user not in session)
     */
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        ?int $userId = null
    ): AuditLog {
        $resolvedUserId = $userId ?? Auth::id();

        return AuditLog::create([
            'user_id' => $resolvedUserId,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
