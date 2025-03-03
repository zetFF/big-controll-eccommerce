<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $fillable = [
        'task_id',
        'status',
        'output',
        'started_at',
        'completed_at',
        'duration',
        'error'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration' => 'float'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
} 