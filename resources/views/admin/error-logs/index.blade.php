@extends('layouts.admin')

@section('title', 'Error Logs')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Error Logs</h1>
    <form action="{{ route('admin.error-logs.cleanup') }}" method="POST" class="inline">
        @csrf
        <select name="days" class="rounded-l-lg border-gray-300">
            <option value="7">7 days</option>
            <option value="30" selected>30 days</option>
            <option value="90">90 days</option>
        </select>
        <button type="submit" 
                class="bg-red-500 text-white px-4 py-2 rounded-r-lg"
                onclick="return confirm('Are you sure you want to clean up old error logs?')">
            Cleanup Old Logs
        </button>
    </form>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Total Errors</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
        <p class="text-sm text-gray-500">Last 7 days</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Most Common Error</h3>
        @php
            $mostCommon = collect($statistics['by_type'])->sort()->last();
            $errorType = collect($statistics['by_type'])->keys()->last();
        @endphp
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $mostCommon ?? 0 }}</p>
        <p class="text-sm text-gray-500">{{ \Str::limit($errorType ?? 'None', 30) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900">Today's Errors</h3>
        <p class="mt-2 text-3xl font-bold text-gray-900">
            {{ $statistics['by_date'][date('Y-m-d')] ?? 0 }}
        </p>
        <p class="text-sm text-gray-500">{{ now()->format('F j, Y') }}</p>
    </div>
</div>

<!-- Filters -->
<div class="mb-6 bg-white rounded-lg shadow p-4">
    <form action="" method="GET" class="grid grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Error Type</label>
            <select name="type" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">All Types</option>
                @foreach(array_keys($statistics['by_type']) as $type)
                    <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                        {{ \Str::limit($type, 40) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Date</label>
            <input type="date" 
                   name="date" 
                   value="{{ request('date') }}"
                   class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Search</label>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Search in message or file..."
                   class="mt-1 block w-full rounded-md border-gray-300">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                Filter
            </button>
            @if(request()->hasAny(['type', 'date', 'search']))
                <a href="{{ route('admin.error-logs.index') }}" 
                   class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Error Logs List -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead>
            <tr>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Message
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Location
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    User
                </th>
                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                </th>
                <th class="px-6 py-3 bg-gray-50"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($errorLogs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ \Str::limit(class_basename($log->type), 20) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $log->short_message }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ \Str::limit($log->file, 30) }}:{{ $log->line }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->user?->name ?? 'Guest' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.error-logs.show', $log) }}" 
                           class="text-blue-600 hover:text-blue-900">View</a>
                        <form action="{{ route('admin.error-logs.destroy', $log) }}" 
                              method="POST" 
                              class="inline ml-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('Are you sure you want to delete this log?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $errorLogs->links() }}
</div>
@endsection 