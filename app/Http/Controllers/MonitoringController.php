<?php

namespace App\Http\Controllers;

use Spatie\Health\ResultStores\ResultStore;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ResultStore $resultStore
    ) {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function dashboard()
    {
        $checkResults = Cache::remember('health_check_results', 300, function () {
            return $this->resultStore->latestResults();
        });

        $systemMetrics = [
            'cpu_usage' => sys_getloadavg()[0],
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];

        return $this->successResponse([
            'health_checks' => $checkResults,
            'system_metrics' => $systemMetrics,
        ]);
    }

    private function getMemoryUsage(): array
    {
        $memInfo = file_get_contents('/proc/meminfo');
        preg_match_all('/(\w+):\s+(\d+)\s/', $memInfo, $matches);
        $memInfo = array_combine($matches[1], $matches[2]);

        $total = $memInfo['MemTotal'];
        $free = $memInfo['MemFree'];
        $used = $total - $free;

        return [
            'total' => round($total / 1024 / 1024, 2), // GB
            'used' => round($used / 1024 / 1024, 2), // GB
            'free' => round($free / 1024 / 1024, 2), // GB
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    private function getDiskUsage(): array
    {
        $disk = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsed = $diskTotal - $disk;

        return [
            'total' => round($diskTotal / 1024 / 1024 / 1024, 2), // GB
            'used' => round($diskUsed / 1024 / 1024 / 1024, 2), // GB
            'free' => round($disk / 1024 / 1024 / 1024, 2), // GB
            'percentage' => round(($diskUsed / $diskTotal) * 100, 2),
        ];
    }
} 