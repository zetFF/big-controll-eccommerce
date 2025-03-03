@extends('layouts.admin')

@section('title', 'System Health')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">System Health</h1>
    @if($lastRanAt)
        <p class="text-sm text-gray-500">Last checked: {{ $lastRanAt->diffForHumans() }}</p>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach($checkResults as $result)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    @if($result->status === Spatie\Health\Enums\Status::OK)
                        <span class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @else
                        <span class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @endif
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            {{ $result->name }}
                        </dt>
                        <dd class="flex items-baseline">
                            <div class="text-lg font-semibold text-gray-900">
                                {{ $result->shortSummary }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
            @if($result->meta)
                <div class="mt-4">
                    <div class="bg-gray-50 rounded p-3">
                        <pre class="text-xs">{{ json_encode($result->meta, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($historicalResults->isNotEmpty())
<div class="mt-8">
    <h2 class="text-lg font-medium mb-4">Historical Results</h2>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Check
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Message
                    </th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($historicalResults as $result)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $result->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $result->status === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($result->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $result->notification_message }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $result->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection 