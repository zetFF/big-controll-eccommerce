<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantValue extends Model
{
    protected $fillable = ['variant_type_id', 'value'];

    public function type()
    {
        return $this->belongsTo(VariantType::class, 'variant_type_id');
    }
} 