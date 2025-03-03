<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'parameters',
        'schedule',
        'last_generated_at',
        'recipients',
        'status',
        'file_path',
    ];

    protected $casts = [
        'parameters' => 'array',
        'schedule' => 'array',
        'recipients' => 'array',
        'last_generated_at' => 'datetime',
    ];

    public const TYPES = [
        'sales',
        'inventory',
        'users',
        'orders',
        'custom'
    ];

    public const STATUSES = [
        'pending',
        'processing',
        'completed',
        'failed'
    ];
} 