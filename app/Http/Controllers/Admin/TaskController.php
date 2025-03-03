<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $tasks = Task::with('creator')
            ->withCount('logs')
            ->latest()
            ->paginate(15);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('admin.tasks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'command' => 'required|string',
            'schedule' => 'required|string',
            'timezone' => 'required|string',
            'metadata' => 'nullable|array'
        ]);

        if (!$this->taskService->validateCronExpression($validated['schedule'])) {
            return back()->withErrors(['schedule' => 'Invalid cron expression']);
        }

        $task = Task::create([
            ...$validated,
            'status' => 'active',
            'created_by' => auth()->id()
        ]);

        $this->taskService->updateNextRunTime($task);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task created successfully');
    }

    public function show(Task $task)
    {
        $stats = $this->taskService->getTaskStats($task);
        $logs = $task->logs()->latest()->paginate(10);

        return view('admin.tasks.show', compact('task', 'stats', 'logs'));
    }

    public function edit(Task $task)
    {
        return view('admin.tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'command' => 'required|string',
            'schedule' => 'required|string',
            'timezone' => 'required|string',
            'status' => 'required|in:' . implode(',', Task::STATUSES),
            'metadata' => 'nullable|array'
        ]);

        if (!$this->taskService->validateCronExpression($validated['schedule'])) {
            return back()->withErrors(['schedule' => 'Invalid cron expression']);
        }

        $task->update($validated);
        $this->taskService->updateNextRunTime($task);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task updated successfully');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully');
    }

    public function run(Task $task)
    {
        $this->taskService->schedule($task);
        return back()->with('success', 'Task executed successfully');
    }
} 