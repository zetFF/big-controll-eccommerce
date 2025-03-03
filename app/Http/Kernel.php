<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // ... existing middlewares ...
        \App\Http\Middleware\AdminMiddleware::class,
        \App\Http\Middleware\SecurityHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // Web middleware group
            \App\Http\Middleware\TrackAnalytics::class,
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'custom.throttle:api',
        ],
    ];

    /**
     * The application's route middleware aliases.
     *
     * Aliases may be used instead of class names to assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        // ... existing middlewares ...
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'custom.throttle' => \App\Http\Middleware\CustomRateLimiter::class,
    ];

    protected $routeMiddleware = [
        // ... existing middleware ...
        'cache.response' => \App\Http\Middleware\CacheResponse::class,
        'ip.ratelimit' => \App\Http\Middleware\IpBasedRateLimiter::class,
        'cache.tags' => \App\Http\Middleware\CacheResponseWithTags::class,
        'ratelimit' => \App\Http\Middleware\RateLimiter::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ];
} 