<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportFailure extends Model
{
    protected $fillable = [
        'import_id',
        'row_number',
        'values',
        'errors'
    ];

    protected $casts = [
        'values' => 'array',
        'errors' => 'array'
    ];

    public function import()
    {
        return $this->belongsTo(Import::class);
    }
} 