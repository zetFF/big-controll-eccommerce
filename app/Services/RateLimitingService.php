<?php

namespace App\Services;

use App\Models\RateLimit;
use App\Models\RateLimitLog;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RateLimitingService
{
    private const CACHE_PREFIX = 'ratelimit:';

    public function check(string $key, string $type): bool
    {
        $rateLimit = $this->getRateLimit($key, $type);
        
        if (!$rateLimit) {
            return true; // No rate limit defined
        }

        $cacheKey = $this->getCacheKey($key, $type);
        $window = $this->getCurrentWindow($rateLimit->window);
        
        if (config('cache.default') === 'redis') {
            return $this->checkRedis($cacheKey, $rateLimit, $window);
        }

        return $this->checkCache($cacheKey, $rateLimit, $window);
    }

    public function increment(string $key, string $type): void
    {
        $rateLimit = $this->getRateLimit($key, $type);
        
        if (!$rateLimit) {
            return;
        }

        $cacheKey = $this->getCacheKey($key, $type);
        $window = $this->getCurrentWindow($rateLimit->window);

        if (config('cache.default') === 'redis') {
            $this->incrementRedis($cacheKey, $window);
        } else {
            $this->incrementCache($cacheKey, $window);
        }

        $this->logRequest($rateLimit, $key, $window);
    }

    public function getRemainingAttempts(string $key, string $type): int
    {
        $rateLimit = $this->getRateLimit($key, $type);
        
        if (!$rateLimit) {
            return -1; // Unlimited
        }

        $cacheKey = $this->getCacheKey($key, $type);
        $window = $this->getCurrentWindow($rateLimit->window);
        $attempts = $this->getCurrentAttempts($cacheKey);

        return max(0, $rateLimit->limit - $attempts);
    }

    private function checkRedis(string $cacheKey, RateLimit $rateLimit, array $window): bool
    {
        $attempts = Redis::get($cacheKey) ?: 0;
        return $attempts < $rateLimit->limit;
    }

    private function checkCache(string $cacheKey, RateLimit $rateLimit, array $window): bool
    {
        $attempts = Cache::get($cacheKey, 0);
        return $attempts < $rateLimit->limit;
    }

    private function incrementRedis(string $cacheKey, array $window): void
    {
        Redis::multi()
            ->incr($cacheKey)
            ->expireat($cacheKey, $window['end']->timestamp)
            ->exec();
    }

    private function incrementCache(string $cacheKey, array $window): void
    {
        $attempts = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $attempts + 1, $window['end']);
    }

    private function getCurrentAttempts(string $cacheKey): int
    {
        if (config('cache.default') === 'redis') {
            return (int) Redis::get($cacheKey) ?: 0;
        }

        return Cache::get($cacheKey, 0);
    }

    private function getRateLimit(string $key, string $type): ?RateLimit
    {
        return Cache::remember(
            "ratelimit_config:{$type}:{$key}",
            now()->addMinutes(60),
            fn() => RateLimit::where('key', $key)
                ->where('type', $type)
                ->first()
        );
    }

    private function getCacheKey(string $key, string $type): string
    {
        return self::CACHE_PREFIX . "{$type}:{$key}:" . $this->getCurrentWindow(60)['start']->timestamp;
    }

    private function getCurrentWindow(int $minutes): array
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->startOfMinute();
        $windowEnd = $windowStart->copy()->addMinutes($minutes);

        return [
            'start' => $windowStart,
            'end' => $windowEnd
        ];
    }

    private function logRequest(RateLimit $rateLimit, string $key, array $window): void
    {
        RateLimitLog::create([
            'rate_limit_id' => $rateLimit->id,
            'key' => $key,
            'requests' => $this->getCurrentAttempts($this->getCacheKey($key, $rateLimit->type)),
            'blocked' => false,
            'window_start' => $window['start'],
            'window_end' => $window['end'],
            'metadata' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'path' => request()->path()
            ]
        ]);
    }
} 