@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold mb-6">Shopping Cart</h1>

        @if($cart->items->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500">Your cart is empty</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Continue Shopping
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($cart->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($item->product->images && count($item->product->images) > 0)
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="{{ Storage::url($item->product->images[0]) }}" 
                                                     alt="">
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $item->product->name }}
                                            </div>
                                            @if($item->attributes)
                                                <div class="text-sm text-gray-500">
                                                    @foreach($item->attributes as $key => $value)
                                                        <span class="mr-2">{{ ucfirst($key) }}: {{ $value }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($item->price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center space-x-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" 
                                               name="quantity" 
                                               value="{{ $item->quantity }}" 
                                               min="1"
                                               class="block w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">Update</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($item->subtotal, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total:</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                {{ number_format($cart->total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <form action="{{ route('cart.clear') }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Clear Cart</button>
                                </form>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Continue Shopping
                        </a>
                        <a href="{{ route('checkout.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 