@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Order #{{ $order->order_number }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium mb-2">Order Details</h3>
                            <p class="text-sm text-gray-600">Order Date: {{ $order->created_at->format('F j, Y') }}</p>
                            <p class="text-sm text-gray-600">Payment Status: {{ ucfirst($order->payment_status) }}</p>
                            <p class="text-sm text-gray-600">Payment Method: {{ ucfirst($order->payment_method) }}</p>
                            @if($order->shipping_tracking_number)
                                <p class="text-sm text-gray-600">Tracking Number: {{ $order->shipping_tracking_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-medium mb-4">Order Items</h3>
                    <div class="border-t border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $item->product_name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($item->price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            {{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium">Subtotal:</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium">Shipping:</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($order->shipping_cost, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium">Tax:</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($order->tax_amount, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="3" class="px-6 py-4 text-right text-base font-medium">Total:</td>
                                    <td class="px-6 py-4 text-right text-base font-medium">{{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 