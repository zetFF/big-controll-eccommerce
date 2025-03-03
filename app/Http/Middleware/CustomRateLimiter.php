<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CustomRateLimiter
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next, string $name = null): Response
    {
        $key = $this->resolveRequestSignature($request, $name ?? 'api');

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts($request))) {
            return response()->json([
                'message' => 'Too many requests',
                'retry_after' => $this->limiter->availableIn($key)
            ], 429);
        }

        $this->limiter->hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        return $response->header('X-RateLimit-Limit', $this->maxAttempts($request))
            ->header('X-RateLimit-Remaining', $this->limiter->remaining($key, $this->maxAttempts($request)));
    }

    protected function resolveRequestSignature(Request $request, string $name): string
    {
        return sha1($name . '|' . $request->ip() . '|' . $request->userAgent());
    }

    protected function maxAttempts(Request $request): int
    {
        return $request->user()?->isAdmin() ? 1000 : 60;
    }

    protected function decayMinutes(): int
    {
        return 1;
    }
} 