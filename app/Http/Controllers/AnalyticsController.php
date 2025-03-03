<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AnalyticsService $analytics
    ) {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function dashboard(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $cacheKey = "analytics:dashboard:{$request->start_date}:{$request->end_date}";

        $data = Cache::remember($cacheKey, 1800, function () use ($request) {
            $pageViews = $this->analytics->getPageViews(
                $request->start_date,
                $request->end_date
            );

            $visitors = [];
            $hourlyTraffic = [];

            foreach ($pageViews as $date => $views) {
                $visitors[$date] = $this->analytics->getUniqueVisitors($date);
                $hourlyTraffic[$date] = $this->analytics->getHourlyTraffic($date);
            }

            return [
                'page_views' => $pageViews,
                'unique_visitors' => $visitors,
                'hourly_traffic' => $hourlyTraffic,
                'total_views' => collect($pageViews)->map(fn($views) => array_sum($views))->sum(),
                'total_visitors' => collect($visitors)->sum(),
            ];
        });

        return $this->successResponse($data);
    }
} 