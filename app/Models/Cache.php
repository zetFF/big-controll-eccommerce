<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cache extends Model
{
    protected $fillable = [
        'key',
        'value',
        'expiration',
        'tags',
        'metadata'
    ];

    protected $casts = [
        'value' => 'array',
        'expiration' => 'datetime',
        'tags' => 'array',
        'metadata' => 'array'
    ];

    public function isExpired(): bool
    {
        return $this->expiration && now()->gte($this->expiration);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiration')
                ->orWhere('expiration', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration', '<=', now());
    }

    public function scopeWithTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }
        return $query;
    }
} 