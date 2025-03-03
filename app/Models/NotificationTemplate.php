<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'channels',
        'subject',
        'content',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'channels' => 'array',
        'metadata' => 'array'
    ];

    public const TYPES = [
        'email',
        'sms',
        'push',
        'database'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 