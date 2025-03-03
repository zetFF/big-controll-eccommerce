<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    private $headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        foreach ($this->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
} 