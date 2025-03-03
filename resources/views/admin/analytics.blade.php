@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="mb-6">
    <!-- Period Selector -->
    <div class="flex space-x-2">
        @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days'] as $value => $label)
        <a href="{{ route('admin.analytics', ['period' => $value]) }}" 
           class="px-4 py-2 rounded-lg {{ $period === $value ? 'bg-blue-500 text-white' : 'bg-white text-gray-700' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 gap-6">
    <!-- API Usage Chart -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-medium">API Usage</h3>
        </div>
        <div class="p-6">
            <canvas id="apiUsageChart"></canvas>
        </div>
    </div>

    <!-- Response Times -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-medium">Response Times</h3>
        </div>
        <div class="p-6">
            <canvas id="responseTimesChart"></canvas>
        </div>
    </div>

    <!-- Error Rates -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-medium">Error Rates</h3>
        </div>
        <div class="p-6">
            <canvas id="errorRatesChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts with metrics data
    const metrics = @json($metrics);
    
    // API Usage Chart
    new Chart(document.getElementById('apiUsageChart'), {
        type: 'line',
        data: {
            labels: metrics.dates,
            datasets: [{
                label: 'API Calls',
                data: metrics.api_calls,
                borderColor: '#3B82F6',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Add more charts initialization
});
</script>
@endpush 