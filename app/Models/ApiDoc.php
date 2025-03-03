<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiDoc extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'version',
        'title',
        'description',
        'spec',
        'is_published',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'spec' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 