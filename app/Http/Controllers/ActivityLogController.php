<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ActivityLogService $activityLogService
    ) {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'action' => 'nullable|string',
            'model_type' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $logs = $this->activityLogService
            ->getActivityLog($filters)
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($logs);
    }

    public function userActivity(Request $request)
    {
        $logs = $this->activityLogService->getUserActivity(auth()->id());
        return $this->successResponse($logs);
    }

    public function modelHistory(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $logs = $this->activityLogService->getModelHistory(
            $request->model_type,
            $request->model_id
        );

        return $this->successResponse($logs);
    }
} 