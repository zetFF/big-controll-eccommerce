<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ActivityLogService
{
    public function log(
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = [],
        ?array $newValues = [],
        ?string $notes = null
    ): void {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes' => $notes
        ]);
    }

    public function getActivityLog(array $filters = []): Builder
    {
        $query = AuditLog::with('user')
            ->latest();

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function getUserActivity(int $userId): Collection
    {
        return AuditLog::where('user_id', $userId)
            ->latest()
            ->take(100)
            ->get();
    }

    public function getModelHistory(string $modelType, int $modelId): Collection
    {
        return AuditLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with('user')
            ->latest()
            ->get();
    }
} 