@extends('layouts.admin')

@section('title', 'Task Scheduler')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Scheduled Tasks</h1>
    <a href="{{ route('admin.tasks.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
        Create New Task
    </a>
</div>

<div class="bg-white rounded-lg shadow">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Schedule
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Last Run
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Next Run
                </th>
                <th class="px-6 py-3 bg-gray-50"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($tasks as $task)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        {{ $task->name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ Str::limit($task->description, 50) }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $task->schedule }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($task->status === 'active') bg-green-100 text-green-800
                        @elseif($task->status === 'failed') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($task->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $task->last_run_at?->diffForHumans() ?? 'Never' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $task->next_run_at?->diffForHumans() ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.tasks.show', $task) }}" class="text-blue-600 hover:text-blue-900">View</a>
                    <a href="{{ route('admin.tasks.edit', $task) }}" class="ml-3 text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form action="{{ route('admin.tasks.run', $task) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="ml-3 text-green-600 hover:text-green-900">Run Now</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $tasks->links() }}
</div>
@endsection 