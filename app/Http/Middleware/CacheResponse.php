<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 3600)
    {
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $cacheKey = 'response_' . sha1($request->fullUrl() . '|' . auth()->id());

        if (Cache::has($cacheKey)) {
            return response()->json(
                Cache::get($cacheKey),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        $response = $next($request);

        if ($response->status() === 200) {
            Cache::put($cacheKey, $response->getData(), $ttl);
        }

        return $response->header('X-Cache', 'MISS');
    }
} 