<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Task;
use App\Services\TaskSchedulingService;
use Illuminate\Http\Request;

class TaskController extends BaseController
{
    public function __construct(
        private TaskSchedulingService $scheduler
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $tasks = Task::with(['creator', 'logs' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'command' => 'required|string',
            'schedule' => 'required|array',
            'schedule.minute' => 'required|string',
            'schedule.hour' => 'required|string',
            'schedule.day' => 'required|string',
            'schedule.month' => 'required|string',
            'schedule.weekday' => 'required|string',
            'timezone' => 'nullable|string',
            'overlap' => 'boolean',
            'maintenance' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        $task = $this->scheduler->scheduleTask($validated);

        return $this->successResponse($task, 201);
    }

    public function show(Task $task)
    {
        return $this->successResponse(
            $task->load(['creator', 'logs' => function ($query) {
                $query->latest()->limit(10);
            }])
        );
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'command' => 'string',
            'schedule' => 'array',
            'schedule.minute' => 'string',
            'schedule.hour' => 'string',
            'schedule.day' => 'string',
            'schedule.month' => 'string',
            'schedule.weekday' => 'string',
            'timezone' => 'nullable|string',
            'overlap' => 'boolean',
            'maintenance' => 'boolean',
            'status' => 'in:' . implode(',', Task::STATUSES),
            'metadata' => 'nullable|array',
        ]);

        $task->update($validated);

        if (isset($validated['schedule'])) {
            $task->next_run_at = $this->scheduler->calculateNextRun($validated['schedule']);
            $task->save();
        }

        return $this->successResponse($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return $this->successResponse(['message' => 'Task deleted successfully']);
    }

    public function execute(Task $task)
    {
        try {
            $log = $this->scheduler->executeTask($task);
            return $this->successResponse([
                'message' => 'Task executed successfully',
                'log' => $log
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Task execution failed: ' . $e->getMessage(), 500);
        }
    }

    public function logs(Task $task)
    {
        $logs = $task->logs()
            ->latest()
            ->paginate(request()->per_page ?? 15);

        return $this->successResponse($logs);
    }
} 