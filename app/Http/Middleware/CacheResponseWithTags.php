<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseWithTags
{
    public function handle(Request $request, Closure $next, string ...$tags): Response
    {
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        $cacheKey = $this->getCacheKey($request);

        if (Cache::tags($tags)->has($cacheKey)) {
            return response()->json(
                Cache::tags($tags)->get($cacheKey),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        $response = $next($request);

        if ($response->status() === 200) {
            Cache::tags($tags)->put(
                $cacheKey,
                $response->getData(),
                now()->addHours(1)
            );
        }

        return $response->header('X-Cache', 'MISS');
    }

    private function getCacheKey(Request $request): string
    {
        return sprintf(
            'response:%s:%s:%s',
            $request->path(),
            md5($request->getQueryString() ?? ''),
            auth()->id() ?? 'guest'
        );
    }
} 