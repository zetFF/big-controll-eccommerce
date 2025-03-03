<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class ProductCacheService
{
    private const TTL = 3600; // 1 hour cache

    public function getProducts(array $filters = []): Collection
    {
        $cacheKey = $this->generateCacheKey($filters);

        return Cache::remember($cacheKey, self::TTL, function () use ($filters) {
            return Product::with(['categories', 'images'])
                ->when(isset($filters['category_id']), function ($q) use ($filters) {
                    return $q->whereHas('categories', function ($q) use ($filters) {
                        $q->where('categories.id', $filters['category_id']);
                    });
                })
                ->when(isset($filters['search']), function ($q) use ($filters) {
                    return $q->where('name', 'like', "%{$filters['search']}%");
                })
                ->when(isset($filters['min_price']), function ($q) use ($filters) {
                    return $q->where('price', '>=', $filters['min_price']);
                })
                ->when(isset($filters['max_price']), function ($q) use ($filters) {
                    return $q->where('price', '<=', $filters['max_price']);
                })
                ->get();
        });
    }

    public function getProduct(int $id): ?Product
    {
        return Cache::remember("product:{$id}", self::TTL, function () use ($id) {
            return Product::with(['categories', 'images'])->find($id);
        });
    }

    public function clearCache(Product $product): void
    {
        Cache::forget("product:{$product->id}");
        Cache::forget($this->generateCacheKey([]));
    }

    private function generateCacheKey(array $filters): string
    {
        $key = 'products';
        
        if (!empty($filters)) {
            $key .= ':' . md5(serialize($filters));
        }

        return $key;
    }
} 