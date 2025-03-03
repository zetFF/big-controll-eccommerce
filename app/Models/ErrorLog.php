<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErrorLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'message',
        'code',
        'file',
        'line',
        'trace',
        'request_method',
        'url',
        'user_id',
        'ip_address',
        'user_agent',
        'additional_data'
    ];

    protected $casts = [
        'trace' => 'array',
        'additional_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getShortMessageAttribute()
    {
        return \Str::limit($this->message, 100);
    }

    public function getFormattedTraceAttribute()
    {
        return collect($this->trace)->map(function ($trace) {
            return [
                'file' => $trace['file'] ?? 'unknown',
                'line' => $trace['line'] ?? 'unknown',
                'function' => $trace['function'] ?? 'unknown',
                'class' => $trace['class'] ?? 'unknown',
            ];
        });
    }
} 