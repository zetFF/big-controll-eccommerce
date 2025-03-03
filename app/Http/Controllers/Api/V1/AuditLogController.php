<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AuditLogController extends BaseController
{
    public function __construct(
        private AuditService $auditService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->user_id, fn($q) => $q->forUser($request->user_id))
            ->when($request->event, fn($q) => $q->forEvent($request->event))
            ->when($request->tags, fn($q) => $q->forTags(explode(',', $request->tags)))
            ->when($request->model, fn($q) => $q->forModel($request->model))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($logs);
    }

    public function show(AuditLog $log)
    {
        return $this->successResponse($log->load('user'));
    }

    public function userActivity(Request $request)
    {
        $activity = $this->auditService->getActivityForUser(
            $request->user_id ?? auth()->id(),
            $request->all()
        );

        return $this->successResponse($activity);
    }

    public function modelHistory(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required'
        ]);

        $modelClass = $request->model_type;
        $model = $modelClass::findOrFail($request->model_id);
        
        $history = $this->auditService->getModelHistory($model);

        return $this->successResponse($history);
    }

    public function stats(Request $request)
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'users_active' => AuditLog::distinct('user_id')->count(),
            'recent_events' => AuditLog::where('created_at', '>=', now()->subDays(7))
                ->groupBy('event')
                ->selectRaw('event, count(*) as count')
                ->pluck('count', 'event')
                ->toArray(),
            'top_models' => AuditLog::where('created_at', '>=', now()->subDays(30))
                ->groupBy('auditable_type')
                ->selectRaw('auditable_type, count(*) as count')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'auditable_type')
                ->toArray()
        ];

        return $this->successResponse($stats);
    }
} 