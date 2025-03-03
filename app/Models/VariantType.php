<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariantType extends Model
{
    protected $fillable = ['name'];

    public function values()
    {
        return $this->hasMany(VariantValue::class);
    }
} 