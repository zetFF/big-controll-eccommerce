@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Activity Log</h1>
    <form action="{{ route('admin.activities.clear') }}" method="POST" class="inline">
        @csrf
        <select name="days" class="rounded-l-lg border-gray-300">
            <option value="30">30 days</option>
            <option value="60">60 days</option>
            <option value="90">90 days</option>
        </select>
        <button type="submit" 
                class="bg-red-500 text-white px-4 py-2 rounded-r-lg"
                onclick="return confirm('Are you sure you want to clear old activities?')">
            Clear Old Activities
        </button>
    </form>
</div>

<!-- Filters -->
<div class="mb-6 bg-white rounded-lg shadow p-4">
    <form action="" method="GET" class="grid grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <select name="type" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">All Types</option>
                <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Authentication</option>
                <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>User</option>
                <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>System</option>
                <option value="backup" {{ request('type') === 'backup' ? 'selected' : '' }}>Backup</option>
                <option value="settings" {{ request('type') === 'settings' ? 'selected' : '' }}>Settings</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">User</label>
            <select name="user" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">All Users</option>
                @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
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
        <div class="flex items-end">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                Filter
            </button>
            @if(request()->hasAny(['type', 'user', 'date']))
                <a href="{{ route('admin.activities.index') }}" 
                   class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Activities List -->
<div class="bg-white rounded-lg shadow divide-y divide-gray-200">
    @foreach($activities as $activity)
        <div class="p-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-{{ $activity->color }}-100">
                            <i class="fas fa-{{ $activity->icon }} text-{{ $activity->color }}-600"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $activity->description }}
                        </p>
                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                            <span>{{ $activity->user?->name ?? 'System' }}</span>
                            <span>&middot;</span>
                            <span>{{ $activity->created_at->diffForHumans() }}</span>
                            <span>&middot;</span>
                            <span>{{ $activity->ip_address }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.activities.show', $activity) }}" 
                       class="text-blue-600 hover:text-blue-900">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $activities->links() }}
</div>
@endsection 