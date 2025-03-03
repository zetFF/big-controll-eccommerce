<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        string $event,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = [],
        array $tags = [],
        array $metadata = []
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model ? $model->getKey() : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => $tags,
            'metadata' => $metadata
        ]);
    }

    public function getActivityForUser(int $userId, array $filters = []): array
    {
        $query = AuditLog::with('user')
            ->forUser($userId);

        if (!empty($filters['event'])) {
            $query->forEvent($filters['event']);
        }

        if (!empty($filters['tags'])) {
            $query->forTags($filters['tags']);
        }

        if (!empty($filters['model'])) {
            $query->forModel($filters['model']);
        }

        return $query->latest()
            ->limit($filters['limit'] ?? 50)
            ->get()
            ->toArray();
    }

    public function getModelHistory(Model $model): array
    {
        return $model->logs()
            ->with('user')
            ->latest()
            ->get()
            ->toArray();
    }

    public function purgeOldLogs(int $daysToKeep = 90): int
    {
        return AuditLog::where('created_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }
} 