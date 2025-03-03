<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class CategoryCacheService
{
    private const TTL = 7200; // 2 hours cache

    public function getCategories(): Collection
    {
        return Cache::remember('categories', self::TTL, function () {
            return Category::with('children')
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();
        });
    }

    public function getCategory(int $id): ?Category
    {
        return Cache::remember("category:{$id}", self::TTL, function () use ($id) {
            return Category::with(['parent', 'children'])->find($id);
        });
    }

    public function clearCache(Category $category = null): void
    {
        Cache::forget('categories');
        
        if ($category) {
            Cache::forget("category:{$category->id}");
        }
    }
} 