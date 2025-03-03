@extends('layouts.app')

@section('title', 'Compare Products')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Compare Products</h1>
            @if($products->isNotEmpty())
                <form action="{{ route('compare.clear') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="text-sm text-red-600 hover:text-red-900">
                        Clear All
                    </button>
                </form>
            @endif
        </div>

        @if($products->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <p class="text-gray-500">No products to compare.</p>
                <a href="{{ route('products.index') }}" 
                   class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Browse Products
                </a>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="grid grid-cols-{{ $products->count() + 1 }} divide-x">
                    <!-- Headers -->
                    <div class="p-4 bg-gray-50">
                        <div class="h-40"></div>
                        <div class="py-2 font-medium">Price</div>
                        <div class="py-2 font-medium">Category</div>
                        <div class="py-2 font-medium">Stock</div>
                        <div class="py-2 font-medium">Rating</div>
                        <div class="py-2 font-medium">Description</div>
                    </div>

                    @foreach($products as $product)
                        <div class="p-4">
                            <div class="relative">
                                <form action="{{ route('compare.remove', $product) }}" 
                                      method="POST" 
                                      class="absolute top-0 right-0">
                                    @csrf
                                    <button type="submit" 
                                            class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Remove</span>
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>

                                <div class="h-40">
                                    @if($product->images && count($product->images) > 0)
                                        <img src="{{ Storage::url($product->images[0]) }}" 
                                             alt="{{ $product->name }}"
                                             class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <h3 class="mt-2 text-sm font-medium">
                                    <a href="{{ route('products.show', $product) }}" class="hover:underline">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                            </div>

                            <div class="py-2">${{ number_format($product->price, 2) }}</div>
                            <div class="py-2">{{ $product->category->name }}</div>
                            <div class="py-2">
                                @if($product->stock > 0)
                                    <span class="text-green-600">In Stock ({{ $product->stock }})</span>
                                @else
                                    <span class="text-red-600">Out of Stock</span>
                                @endif
                            </div>
                            <div class="py-2">
                                <div class="flex items-center">
                                    <span class="mr-1">{{ number_format($product->average_rating, 1) }}</span>
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= round($product->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}"
                                                 fill="currentColor"
                                                 viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="ml-1 text-sm text-gray-500">({{ $product->reviews_count }})</span>
                                </div>
                            </div>
                            <div class="py-2">
                                <p class="text-sm text-gray-500">
                                    {{ Str::limit($product->description, 100) }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <form action="{{ route('cart.add', $product) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            @if($product->stock == 0) disabled @endif
                                            class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 