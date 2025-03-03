<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class FileCollection extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'description',
        'type',
        'visibility',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeVisible($query)
    {
        return $query->where('visibility', 'public');
    }
} 