<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class ErrorTrackingService
{
    public function log(Throwable $exception, array $additionalData = []): ErrorLog
    {
        return ErrorLog::create([
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $this->formatTrace($exception->getTrace()),
            'request_method' => Request::method(),
            'url' => Request::fullUrl(),
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'additional_data' => array_merge([
                'session_id' => session()->getId(),
                'inputs' => Request::except(['password', 'password_confirmation']),
                'headers' => Request::header()
            ], $additionalData)
        ]);
    }

    protected function formatTrace(array $trace): array
    {
        return collect($trace)
            ->map(function ($item) {
                // Remove arguments from trace to prevent sensitive data logging
                unset($item['args']);
                return $item;
            })
            ->take(20) // Limit trace depth
            ->toArray();
    }

    public function cleanup(int $days = 30): int
    {
        return ErrorLog::where('created_at', '<', now()->subDays($days))->delete();
    }

    public function getStatistics(int $days = 7): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        return [
            'total' => ErrorLog::where('created_at', '>=', $startDate)->count(),
            'by_type' => ErrorLog::where('created_at', '>=', $startDate)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray(),
            'by_date' => ErrorLog::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->get()
                ->pluck('count', 'date')
                ->toArray()
        ];
    }
} 