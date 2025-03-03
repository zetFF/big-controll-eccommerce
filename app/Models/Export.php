<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Export extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'status',
        'file_path',
        'file_name',
        'file_size',
        'filters',
        'columns',
        'format',
        'created_by',
        'completed_at',
        'metadata'
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'file_size' => 'integer',
        'completed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public const STATUSES = [
        'pending',
        'processing',
        'completed',
        'failed'
    ];

    public const FORMATS = [
        'csv',
        'xlsx',
        'pdf',
        'json'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 