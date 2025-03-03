<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class SearchIndex extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'content',
        'type',
        'searchable_type',
        'searchable_id',
        'metadata',
        'permissions'
    ];

    protected $casts = [
        'metadata' => 'array',
        'permissions' => 'array'
    ];

    public function searchable()
    {
        return $this->morphTo();
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'searchable_type' => $this->searchable_type,
            'searchable_id' => $this->searchable_id,
            'metadata' => $this->metadata,
            'permissions' => $this->permissions,
            'created_at' => $this->created_at->timestamp
        ];
    }

    public function shouldBeSearchable()
    {
        return true;
    }
} 