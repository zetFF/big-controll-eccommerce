<?php

namespace App\Services;

use App\Models\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache as FacadesCache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CacheService
{
    private const DEFAULT_TTL = 3600; // 1 hour

    public function get(string $key, array $tags = [])
    {
        $cache = Cache::active()
            ->when(!empty($tags), fn($q) => $q->withTags($tags))
            ->where('key', $key)
            ->first();

        if (!$cache) {
            return null;
        }

        return $cache->value;
    }

    public function set(
        string $key,
        $value,
        ?Carbon $expiration = null,
        array $tags = [],
        array $metadata = []
    ): Cache {
        return Cache::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'expiration' => $expiration,
                'tags' => $tags,
                'metadata' => $metadata
            ]
        );
    }

    public function forget(string $key): bool
    {
        return Cache::where('key', $key)->delete() > 0;
    }

    public function flush(array $tags = []): int
    {
        $query = Cache::query();
        
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', $tag);
            }
        }

        return $query->delete();
    }

    public function remember(
        string $key,
        \Closure $callback,
        ?Carbon $expiration = null,
        array $tags = [],
        array $metadata = []
    ) {
        $value = $this->get($key, $tags);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        $this->set($key, $value, $expiration, $tags, $metadata);

        return $value;
    }

    public function tags(array $tags): self
    {
        return new class($tags, $this) {
            private $tags;
            private $service;

            public function __construct(array $tags, CacheService $service)
            {
                $this->tags = $tags;
                $this->service = $service;
            }

            public function get(string $key)
            {
                return $this->service->get($key, $this->tags);
            }

            public function set(string $key, $value, ?Carbon $expiration = null, array $metadata = [])
            {
                return $this->service->set($key, $value, $expiration, $this->tags, $metadata);
            }

            public function forget(string $key)
            {
                return $this->service->forget($key);
            }

            public function flush()
            {
                return $this->service->flush($this->tags);
            }
        };
    }

    public function cleanup(): int
    {
        return Cache::expired()->delete();
    }

    public function stats(): array
    {
        return [
            'total_entries' => Cache::count(),
            'active_entries' => Cache::active()->count(),
            'expired_entries' => Cache::expired()->count(),
            'size_bytes' => Cache::sum('LENGTH(value)'),
            'tags_distribution' => $this->getTagsDistribution(),
            'expiration_distribution' => $this->getExpirationDistribution()
        ];
    }

    private function getTagsDistribution(): array
    {
        $distribution = [];
        $caches = Cache::select('tags')->get();

        foreach ($caches as $cache) {
            foreach ($cache->tags as $tag) {
                $distribution[$tag] = ($distribution[$tag] ?? 0) + 1;
            }
        }

        arsort($distribution);
        return array_slice($distribution, 0, 10);
    }

    private function getExpirationDistribution(): array
    {
        return [
            'no_expiration' => Cache::whereNull('expiration')->count(),
            'expired' => Cache::expired()->count(),
            'expires_in_hour' => Cache::where('expiration', '>', now())
                ->where('expiration', '<=', now()->addHour())
                ->count(),
            'expires_in_day' => Cache::where('expiration', '>', now()->addHour())
                ->where('expiration', '<=', now()->addDay())
                ->count(),
            'expires_in_week' => Cache::where('expiration', '>', now()->addDay())
                ->where('expiration', '<=', now()->addWeek())
                ->count(),
            'expires_later' => Cache::where('expiration', '>', now()->addWeek())->count()
        ];
    }

    public function rememberModel(Model $model): Model
    {
        $key = $this->getModelCacheKey($model);
        return $this->remember($key, $model);
    }

    public function rememberCollection(string $key, Collection $collection): Collection
    {
        return $this->remember($key, $collection);
    }

    public function invalidateModel(Model $model): void
    {
        Cache::forget($this->getModelCacheKey($model));
    }

    public function invalidateTag(string $tag): void
    {
        Cache::tags($tag)->flush();
    }

    public function warmup(array $keys): void
    {
        foreach ($keys as $key => $callback) {
            if (!Cache::has($key)) {
                $this->remember($key, $callback);
            }
        }
    }

    public function getStats(): array
    {
        if (config('cache.default') === 'redis') {
            return $this->getRedisStats();
        }

        return [
            'driver' => config('cache.default'),
            'status' => 'connected'
        ];
    }

    private function getModelCacheKey(Model $model): string
    {
        return sprintf(
            '%s:%s:%s',
            class_basename($model),
            $model->getKey(),
            $model->updated_at?->timestamp ?? 'new'
        );
    }

    private function getRedisStats(): array
    {
        $info = Redis::info();
        return [
            'driver' => 'redis',
            'status' => 'connected',
            'used_memory' => $info['used_memory_human'] ?? 'N/A',
            'connected_clients' => $info['connected_clients'] ?? 0,
            'last_save_time' => $info['last_save_time'] ?? 0,
            'total_keys' => $info['db0']['keys'] ?? 0,
        ];
    }
} 