<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimit extends Model
{
    protected $fillable = [
        'key',
        'type',
        'limit',
        'window',
        'description',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'limit' => 'integer',
        'window' => 'integer',
        'metadata' => 'array'
    ];

    public const TYPES = [
        'ip',
        'user',
        'token',
        'custom'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(RateLimitLog::class);
    }
} 