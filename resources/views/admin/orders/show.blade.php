@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Order #{{ $order->order_number }}</h1>
    <div class="flex gap-2">
        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="inline-flex">
            @csrf
            @method('PUT')
            <select name="status" 
                    onchange="this.form.submit()"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                    <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </form>

        <form action="{{ route('admin.orders.update-payment', $order) }}" method="POST" class="inline-flex">
            @csrf
            @method('PUT')
            <select name="payment_status" 
                    onchange="this.form.submit()"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach(['pending', 'paid', 'failed'] as $status)
                    <option value="{{ $status }}" {{ $order->payment_status === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium mb-2">Customer Information</h3>
                <p class="text-sm text-gray-600">Name: {{ $order->user->name }}</p>
                <p class="text-sm text-gray-600">Email: {{ $order->user->email }}</p>
            </div>
            <div>
                <h3 class="text-lg font-medium mb-2">Order Information</h3>
                <p class="text-sm text-gray-600">Order Date: {{ $order->created_at->format('F j, Y H:i') }}</p>
                <p class="text-sm text-gray-600">Payment Method: {{ ucfirst($order->payment_method) }}</p>
                <p class="text-sm text-gray-600">Shipping Method: {{ ucfirst($order->shipping_method) }}</p>
                @if($order->shipping_tracking_number)
                    <p class="text-sm text-gray-600">Tracking Number: {{ $order->shipping_tracking_number }}</p>
                @endif
            </div>
        </div>

        <div class="mt-8">
            <h3 class="text-lg font-medium mb-4">Order Items</h3>
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
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                @if($item->metadata)
                                    <div class="text-sm text-gray-500">
                                        @foreach($item->metadata as $key => $value)
                                            {{ ucfirst($key) }}: {{ $value }}<br>
                                        @endforeach
                                    </div>
                                @endif
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

        @if($order->notes)
            <div class="mt-8">
                <h3 class="text-lg font-medium mb-2">Order Notes</h3>
                <p class="text-sm text-gray-600">{{ $order->notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection 