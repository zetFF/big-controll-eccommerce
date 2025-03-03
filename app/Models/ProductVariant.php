<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'variant_values'
    ];

    protected $casts = [
        'variant_values' => 'array',
        'price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('order');
    }

    public function getVariantValuesAttribute($value)
    {
        $values = json_decode($value, true);
        return VariantValue::whereIn('id', $values)->get();
    }
} 