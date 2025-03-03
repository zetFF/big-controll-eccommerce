<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'events',
        'secret',
        'is_active',
        'headers',
        'retry_count',
        'timeout',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
        'retry_count' => 'integer',
        'timeout' => 'integer',
        'metadata' => 'array'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }
} 