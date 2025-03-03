<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ReportingService $reportingService
    ) {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function salesReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $cacheKey = "sales_report:{$request->start_date}:{$request->end_date}";

        $report = Cache::remember($cacheKey, 3600, function () use ($request) {
            return $this->reportingService->getSalesReport(
                $request->start_date,
                $request->end_date
            );
        });

        return $this->successResponse($report);
    }

    public function topProducts(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'limit' => 'integer|min:1|max:100',
        ]);

        $cacheKey = "top_products:{$request->start_date}:{$request->end_date}:{$request->limit}";

        $products = Cache::remember($cacheKey, 3600, function () use ($request) {
            return $this->reportingService->getTopProducts(
                $request->start_date,
                $request->end_date,
                $request->limit ?? 10
            );
        });

        return $this->successResponse($products);
    }

    public function customerStats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $cacheKey = "customer_stats:{$request->start_date}:{$request->end_date}";

        $stats = Cache::remember($cacheKey, 3600, function () use ($request) {
            return $this->reportingService->getCustomerStats(
                $request->start_date,
                $request->end_date
            );
        });

        return $this->successResponse($stats);
    }
} 