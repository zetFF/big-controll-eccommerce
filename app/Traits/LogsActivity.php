<?php

namespace App\Traits;

use App\Models\Activity;
use App\Services\ActivityService;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            app(ActivityService::class)->log(
                $model->getActivityType(),
                'created',
                "{$model->getActivitySubject()} was created",
                $model
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            
            if (!empty($changes)) {
                app(ActivityService::class)->log(
                    $model->getActivityType(),
                    'updated',
                    "{$model->getActivitySubject()} was updated",
                    $model,
                    $changes
                );
            }
        });

        static::deleted(function ($model) {
            app(ActivityService::class)->log(
                $model->getActivityType(),
                'deleted',
                "{$model->getActivitySubject()} was deleted",
                $model
            );
        });
    }

    public function getActivityType(): string
    {
        return $this->activityType ?? strtolower(class_basename($this));
    }

    public function getActivitySubject(): string
    {
        return $this->activitySubject ?? class_basename($this);
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }
} 