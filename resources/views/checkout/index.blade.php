@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            <div>
                <h1 class="text-2xl font-semibold mb-6">Checkout</h1>
                
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf
                    
                    <!-- Order Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-medium mb-4">Order Summary</h2>
                        <div class="space-y-4">
                            @foreach($cart->items as $item)
                                <div class="flex items-center">
                                    @if($item->product->images && count($item->product->images) > 0)
                                        <img src="{{ Storage::url($item->product->images[0]) }}" 
                                             alt="" 
                                             class="h-16 w-16 object-cover rounded">
                                    @endif
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium">{{ $item->product->name }}</h3>
                                        <p class="text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                                        @if($item->attributes)
                                            <p class="text-sm text-gray-500">
                                                @foreach($item->attributes as $key => $value)
                                                    {{ ucfirst($key) }}: {{ $value }}
                                                @endforeach
                                            </p>
                                        @endif
                                    </div>
                                    <p class="text-sm font-medium">
                                        {{ number_format($item->subtotal, 2) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Shipping Method -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-medium mb-4">Shipping Method</h2>
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="shipping_method" 
                                       value="standard"
                                       checked
                                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="ml-3">
                                    <span class="block text-sm font-medium">Standard Shipping</span>
                                    <span class="block text-sm text-gray-500">4-10 business days</span>
                                </span>
                                <span class="ml-auto">$10.00</span>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-medium mb-4">Payment Method</h2>
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="payment_method" 
                                       value="card"
                                       checked
                                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="ml-3">
                                    <span class="block text-sm font-medium">Credit/Debit Card</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-medium mb-4">Order Notes</h2>
                        <textarea name="notes" 
                                  rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                  placeholder="Special instructions for delivery"></textarea>
                    </div>
                </div>

                <!-- Order Total -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-medium mb-4">Order Total</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal</span>
                            <span>{{ number_format($cart->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Shipping</span>
                            <span>$10.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Tax (10%)</span>
                            <span>{{ number_format($cart->total * 0.1, 2) }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex justify-between text-base font-medium">
                                <span>Total</span>
                                <span>{{ number_format($cart->total + 10 + ($cart->total * 0.1), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full bg-blue-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Place Order
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('cart.index') }}" class="text-sm text-blue-600 hover:text-blue-500">
                            Return to Cart
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 