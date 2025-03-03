<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;

class TrackAnalytics
{
    public function __construct(
        private AnalyticsService $analytics
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        try {
            $path = $request->path();
            $this->analytics->trackPageView($path, auth()->id());

            if ($request->is('products/*')) {
                $this->analytics->trackEvent('product_view', [
                    'product_id' => $request->route('product')->id ?? null,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't interrupt the request
            \Log::error('Analytics tracking failed: ' . $e->getMessage());
        }

        return $response;
    }
} 