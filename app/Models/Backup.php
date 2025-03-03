<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'disk',
        'path',
        'size',
        'type',
        'status',
        'created_by',
        'completed_at',
        'metadata'
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'completed_at' => 'datetime'
    ];

    public const TYPES = [
        'full',
        'database',
        'files',
        'custom'
    ];

    public const STATUSES = [
        'pending',
        'in_progress',
        'completed',
        'failed'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDownloadUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeForHumansAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function delete()
    {
        Storage::disk($this->disk)->delete($this->path);
        return parent::delete();
    }
} 