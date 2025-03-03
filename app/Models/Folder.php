<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullPathAttribute(): string
    {
        return $this->parent
            ? $this->parent->full_path . '/' . $this->name
            : $this->name;
    }
} 