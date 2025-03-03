<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock',
        'sku',
        'is_active',
        'is_featured',
        'images',
        'attributes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
        'attributes' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = Str::upper(Str::random(8));
            }
        });
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first();
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price) return 0;
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function getMainImageUrlAttribute()
    {
        return $this->images[0] ?? null;
    }

    public function inStock()
    {
        return $this->stock > 0;
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')
            ->withTimestamps();
    }

    public function getRelatedProducts($limit = 4)
    {
        return static::where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    public function getRecommendedProducts($limit = 4)
    {
        // Get products that other users also bought
        $similarProductIds = Order::whereHas('items', function ($query) {
            $query->where('product_id', $this->id);
        })
        ->with(['items' => function ($query) {
            $query->where('product_id', '!=', $this->id);
        }])
        ->get()
        ->pluck('items')
        ->flatten()
        ->pluck('product_id')
        ->unique();

        return static::whereIn('id', $similarProductIds)
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePopular($query)
    {
        return $query->withCount(['orderItems as total_sold' => function($query) {
            $query->whereHas('order', function($q) {
                $q->where('status', 'completed');
            });
        }])
        ->orderByDesc('total_sold');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function bundles()
    {
        return $this->belongsToMany(ProductBundle::class, 'bundle_products')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function hasVariants()
    {
        return $this->variants()->exists();
    }

    public function getDefaultVariantAttribute()
    {
        return $this->variants()->first();
    }

    public function getAvailableVariantTypesAttribute()
    {
        $variantValueIds = $this->variants->pluck('variant_values')->flatten()->pluck('id');
        $values = VariantValue::whereIn('id', $variantValueIds)->with('type')->get();
        
        return $values->groupBy('type.name');
    }
} 