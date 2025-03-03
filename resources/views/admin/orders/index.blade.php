@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Orders</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b">
        <div class="flex gap-4">
            <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
               class="px-3 py-1 rounded-full text-sm font-medium {{ !request('status') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                All
            </a>
            @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                <a href="{{ request()->fullUrlWithQuery(['status' => $status]) }}" 
                   class="px-3 py-1 rounded-full text-sm font-medium {{ request('status') === $status ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($status) }}
                </a>
            @endforeach
        </div>
    </div>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($orders as $order)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            #{{ $order->order_number }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $order->items->count() }} items
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $order->user->name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $order->user->email }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ number_format($order->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                            @elseif($order->status === 'delivered') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($order->payment_status === 'paid') bg-green-100 text-green-800
                            @elseif($order->payment_status === 'pending') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $order->created_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">
        {{ $orders->links() }}
    </div>
</div>
@endsection 