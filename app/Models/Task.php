<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Task extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'description',
        'command',
        'schedule',
        'timezone',
        'status',
        'last_run_at',
        'next_run_at',
        'created_by',
        'output',
        'metadata'
    ];

    protected $casts = [
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'metadata' => 'array'
    ];

    public const STATUSES = [
        'active',
        'inactive',
        'running',
        'failed'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(TaskLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDue($query)
    {
        return $query->where('next_run_at', '<=', now());
    }
} 