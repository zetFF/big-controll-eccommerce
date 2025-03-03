@extends('layouts.admin')

@section('title', 'Error Log Details')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Error Log Details</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.error-logs.index') }}" 
               class="bg-gray-500 text-white px-4 py-2 rounded-lg">
                Back to List
            </a>
            <form action="{{ route('admin.error-logs.destroy', $errorLog) }}" 
                  method="POST" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-500 text-white px-4 py-2 rounded-lg"
                        onclick="return confirm('Are you sure you want to delete this log?')">
                    Delete Log
                </button>
            </form>
        </div>
    </div>
    <p class="text-sm text-gray-500">Occurred {{ $errorLog->created_at->diffForHumans() }}</p>
</div>

<div class="grid grid-cols-1 gap-6">
    <!-- Basic Information -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Error Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->type }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Error Code</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->code ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Message</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->message }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">File</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->file }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Line</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->line }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Request Information -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Information</h3>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">URL</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->url }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Method</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->request_method }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->ip_address }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->user_agent }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">User</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $errorLog->user?->name ?? 'Guest' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Stack Trace -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Stack Trace</h3>
            <div class="bg-gray-50 rounded p-4">
                <pre class="text-xs overflow-x-auto">{{ json_encode($errorLog->formatted_trace, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>

    <!-- Additional Data -->
    @if($errorLog->additional_data)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Data</h3>
            <div class="bg-gray-50 rounded p-4">
                <pre class="text-xs overflow-x-auto">{{ json_encode($errorLog->additional_data, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection 