<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimitLog extends Model
{
    protected $fillable = [
        'rate_limit_id',
        'key',
        'requests',
        'blocked',
        'window_start',
        'window_end',
        'metadata'
    ];

    protected $casts = [
        'requests' => 'integer',
        'blocked' => 'boolean',
        'window_start' => 'datetime',
        'window_end' => 'datetime',
        'metadata' => 'array'
    ];

    public function rateLimit()
    {
        return $this->belongsTo(RateLimit::class);
    }
} 