<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsService
{
    public function trackPageView(string $page, ?int $userId = null): void
    {
        $date = now()->format('Y-m-d');
        
        Redis::pipeline(function ($pipe) use ($page, $date, $userId) {
            // Increment daily page views
            $pipe->hincrby("analytics:pageviews:{$date}", $page, 1);
            
            // Track unique visitors
            if ($userId) {
                $pipe->pfadd("analytics:visitors:{$date}", $userId);
            } else {
                $pipe->pfadd("analytics:visitors:{$date}", request()->ip());
            }
            
            // Track hourly traffic
            $hour = now()->format('H');
            $pipe->hincrby("analytics:hourly:{$date}", $hour, 1);
        });
    }

    public function trackEvent(string $event, array $properties = []): void
    {
        $data = [
            'event' => $event,
            'properties' => $properties,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        Redis::rpush('analytics:events', json_encode($data));
    }

    public function getPageViews(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $results = [];

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $views = Redis::hgetall("analytics:pageviews:{$dateStr}");
            $results[$dateStr] = $views ?: [];
        }

        return $results;
    }

    public function getUniqueVisitors(string $date): int
    {
        return Redis::pfcount("analytics:visitors:{$date}");
    }

    public function getHourlyTraffic(string $date): array
    {
        return Redis::hgetall("analytics:hourly:{$date}") ?: [];
    }
} 