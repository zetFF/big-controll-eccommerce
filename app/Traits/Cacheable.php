<?php

namespace App\Traits;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait Cacheable
{
    protected static function bootCacheable(): void
    {
        static::saved(function (Model $model) {
            app(CacheService::class)->invalidateModel($model);
        });

        static::deleted(function (Model $model) {
            app(CacheService::class)->invalidateModel($model);
        });
    }

    public function getCached(): Model
    {
        return app(CacheService::class)->rememberModel($this);
    }

    public static function getCachedBy(string $key, $value): ?Model
    {
        $cacheKey = sprintf('%s:%s:%s', class_basename(static::class), $key, $value);
        
        return app(CacheService::class)->remember(
            $cacheKey,
            fn() => static::where($key, $value)->first()
        );
    }

    public static function getAllCached(): Collection
    {
        $cacheKey = sprintf('%s:all', class_basename(static::class));
        
        return app(CacheService::class)->remember(
            $cacheKey,
            fn() => static::all()
        );
    }
} 