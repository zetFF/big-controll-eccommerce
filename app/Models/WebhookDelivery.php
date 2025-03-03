<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response',
        'status_code',
        'processing_time',
        'attempt',
        'error',
        'scheduled_at',
        'delivered_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'status_code' => 'integer',
        'processing_time' => 'float',
        'attempt' => 'integer',
        'scheduled_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
} 