@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold mb-6">My Wishlist</h1>

        @if($wishlist->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500">Your wishlist is empty.</p>
                <a href="{{ route('products.index') }}" 
                   class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Browse Products
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($wishlist as $product)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        @if($product->images && count($product->images) > 0)
                            <img src="{{ Storage::url($product->images[0]) }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-48 object-cover">
                        @endif
                        <div class="p-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="{{ route('products.show', $product) }}" class="hover:underline">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $product->category->name }}
                            </p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-lg font-medium text-gray-900">
                                    ${{ number_format($product->price, 2) }}
                                </span>
                                @if($product->stock > 0)
                                    <span class="text-sm text-green-600">In Stock</span>
                                @else
                                    <span class="text-sm text-red-600">Out of Stock</span>
                                @endif
                            </div>
                            <div class="mt-4 flex items-center justify-between space-x-2">
                                <form action="{{ route('cart.add', $product) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                            @if($product->stock == 0) disabled @endif
                                            class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Add to Cart
                                    </button>
                                </form>
                                <form action="{{ route('wishlist.destroy', $product) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="flex items-center justify-center p-2 border border-gray-300 rounded-md text-gray-500 hover:text-gray-600 hover:border-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $wishlist->links() }}
            </div>
        @endif
    </div>
</div>
@endsection 