<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'properties' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function getIconAttribute()
    {
        return match($this->type) {
            'auth' => 'key',
            'user' => 'user',
            'system' => 'cog',
            'backup' => 'archive',
            'settings' => 'sliders',
            default => 'activity'
        };
    }

    public function getColorAttribute()
    {
        return match($this->type) {
            'auth' => 'blue',
            'user' => 'green',
            'system' => 'purple',
            'backup' => 'yellow',
            'settings' => 'pink',
            default => 'gray'
        };
    }
} 