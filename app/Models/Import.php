<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'status',
        'file_path',
        'file_name',
        'file_size',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'mapping',
        'created_by',
        'completed_at',
        'metadata'
    ];

    protected $casts = [
        'mapping' => 'array',
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'processed_rows' => 'integer',
        'failed_rows' => 'integer',
        'completed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public const STATUSES = [
        'pending',
        'processing',
        'validating',
        'completed',
        'failed'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function failures()
    {
        return $this->hasMany(ImportFailure::class);
    }
} 