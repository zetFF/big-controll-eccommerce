<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBundle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_amount',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getSavingsAttribute()
    {
        $totalPrice = $this->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });
        
        return $totalPrice - $this->price;
    }

    public function getSavingsPercentageAttribute()
    {
        $totalPrice = $this->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });
        
        return round(($this->savings / $totalPrice) * 100);
    }
} 