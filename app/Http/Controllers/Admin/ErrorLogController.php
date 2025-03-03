<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Services\ErrorTrackingService;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    public function __construct(
        private ErrorTrackingService $errorTrackingService
    ) {}

    public function index(Request $request)
    {
        $query = ErrorLog::with('user')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->date, fn($q) => $q->whereDate('created_at', $request->date))
            ->when($request->search, function($q) use ($request) {
                $q->where('message', 'like', "%{$request->search}%")
                  ->orWhere('file', 'like', "%{$request->search}%");
            });

        $errorLogs = $query->latest()->paginate(20);
        $statistics = $this->errorTrackingService->getStatistics();

        return view('admin.error-logs.index', compact('errorLogs', 'statistics'));
    }

    public function show(ErrorLog $errorLog)
    {
        return view('admin.error-logs.show', compact('errorLog'));
    }

    public function destroy(ErrorLog $errorLog)
    {
        $errorLog->delete();
        return back()->with('success', 'Error log deleted successfully');
    }

    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $count = $this->errorTrackingService->cleanup($days);
        
        return back()->with('success', "Cleaned up {$count} error logs older than {$days} days");
    }
} 