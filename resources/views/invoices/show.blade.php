@extends('layouts.app')

@section('title', 'Invoice #' . $order->order_number)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-semibold">Invoice #{{ $order->order_number }}</h1>
            <a href="{{ route('invoices.download', $order) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                Download PDF
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-8">
                <!-- Header -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ config('app.name') }}</h2>
                        <p class="text-gray-600">123 Business Street</p>
                        <p class="text-gray-600">City, Country 12345</p>
                        <p class="text-gray-600">contact@example.com</p>
                    </div>
                    <div class="text-right">
                        <h3 class="text-lg font-semibold text-gray-900">Invoice To:</h3>
                        <p class="text-gray-600">{{ $order->user->name }}</p>
                        <p class="text-gray-600">{{ $order->user->email }}</p>
                        <p class="text-gray-600">Order Date: {{ $order->created_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <!-- Order Items -->
                <table class="min-w-full divide-y divide-gray-200 mb-8">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ $item->product_name }}</div>
                                    @if($item->metadata)
                                        <div class="text-sm text-gray-500">
                                            @foreach($item->metadata as $key => $value)
                                                {{ ucfirst($key) }}: {{ $value }}
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">
                                    {{ number_format($item->price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900">
                                    {{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-medium">Subtotal:</span>
                        <span>{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-medium">Shipping:</span>
                        <span>{{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm mb-4">
                        <span class="font-medium">Tax:</span>
                        <span>{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span>{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 mt-8 pt-8 text-sm text-gray-600">
                    <p class="mb-2">Payment Method: {{ ucfirst($order->payment_method) }}</p>
                    <p class="mb-2">Payment Status: {{ ucfirst($order->payment_status) }}</p>
                    @if($order->notes)
                        <p class="mb-2">Notes: {{ $order->notes }}</p>
                    @endif
                    <p class="mt-8 text-center">Thank you for your business!</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 