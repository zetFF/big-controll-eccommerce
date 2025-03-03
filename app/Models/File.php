<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class File extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'extension',
        'checksum',
        'visibility',
        'fileable_type',
        'fileable_id',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array'
    ];

    public const VISIBILITIES = [
        'private',
        'public'
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUrlAttribute()
    {
        return $this->visibility === 'public'
            ? Storage::disk($this->disk)->url($this->path)
            : null;
    }

    public function getFullPathAttribute()
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function scopeVisible($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('mime_type', 'LIKE', $type . '/%');
    }
} 