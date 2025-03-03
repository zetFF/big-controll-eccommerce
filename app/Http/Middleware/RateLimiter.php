<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RateLimitingService;
use Symfony\Component\HttpFoundation\Response;

class RateLimiter
{
    public function __construct(
        private RateLimitingService $rateLimiter
    ) {}

    public function handle(Request $request, Closure $next, string $type = 'ip'): Response
    {
        $key = $this->getKey($request, $type);

        if (!$this->rateLimiter->check($key, $type)) {
            return response()->json([
                'message' => 'Too Many Requests',
                'remaining_attempts' => 0,
                'retry_after' => now()->addMinutes(1)->timestamp
            ], 429);
        }

        $response = $next($request);

        $this->rateLimiter->increment($key, $type);

        return $response->header(
            'X-RateLimit-Remaining',
            $this->rateLimiter->getRemainingAttempts($key, $type)
        );
    }

    private function getKey(Request $request, string $type): string
    {
        return match ($type) {
            'ip' => $request->ip(),
            'user' => $request->user()?->id ?? 'guest',
            'token' => $request->bearerToken() ?? 'guest',
            default => $request->ip()
        };
    }
} 