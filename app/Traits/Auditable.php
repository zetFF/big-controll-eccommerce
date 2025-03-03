<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function (Model $model) {
            static::audit('created', $model);
        });

        static::updated(function (Model $model) {
            static::audit('updated', $model);
        });

        static::deleted(function (Model $model) {
            static::audit('deleted', $model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                static::audit('restored', $model);
            });
        }
    }

    protected static function audit(string $event, Model $model)
    {
        if (!auth()->check()) {
            return;
        }

        $request = request();
        $changes = [];

        if ($event === 'created') {
            $changes['new_values'] = $model->getAttributes();
        } elseif ($event === 'updated') {
            $changes = [
                'old_values' => array_intersect_key($model->getOriginal(), $model->getDirty()),
                'new_values' => $model->getDirty()
            ];
        } elseif ($event === 'deleted') {
            $changes['old_values'] = $model->getAttributes();
        }

        if (empty($changes['new_values'] ?? []) && empty($changes['old_values'] ?? [])) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $changes['old_values'] ?? [],
            'new_values' => $changes['new_values'] ?? [],
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => static::getAuditTags($model),
            'metadata' => static::getAuditMetadata($model)
        ]);
    }

    protected static function getAuditTags(Model $model): array
    {
        return property_exists($model, 'auditTags') ? $model->auditTags : [];
    }

    protected static function getAuditMetadata(Model $model): array
    {
        return method_exists($model, 'getAuditMetadata') 
            ? $model->getAuditMetadata() 
            : [];
    }

    public function logs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
} 