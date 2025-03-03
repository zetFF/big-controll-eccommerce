@extends('layouts.app')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
<!-- Hero Section -->
<div class="relative bg-gray-900">
    <div class="relative h-96 overflow-hidden">
        <!-- Background Image -->
        <img src="{{ asset('images/hero-bg.jpg') }}" 
             alt="Hero background" 
             class="w-full h-full object-cover opacity-50">
        
        <!-- Content -->
        <div class="absolute inset-0 flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl md:text-6xl">
                    Welcome to {{ config('app.name') }}
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-xl text-gray-300">
                    Discover our amazing collection of products
                </p>
                <div class="mt-10">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Shop Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Categories -->
@if($featuredCategories->isNotEmpty())
    <div class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">Shop by Category</h2>
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($featuredCategories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                       class="group">
                        <div class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100">
                            @if($category->image)
                                <img src="{{ Storage::url($category->image) }}" 
                                     alt="{{ $category->name }}"
                                     class="w-full h-full object-center object-cover group-hover:opacity-75">
                            @endif
                        </div>
                        <h3 class="mt-4 text-sm text-gray-700">{{ $category->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $category->products_count }} products</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- Popular Products -->
@if($popularProducts->isNotEmpty())
    <div class="bg-gray-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">Popular Products</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($popularProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- New Arrivals -->
@if($newArrivals->isNotEmpty())
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8">New Arrivals</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- Features Section -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Free Shipping -->
            <div class="text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium">Free Shipping</h3>
                <p class="mt-2 text-sm text-gray-500">On orders over $100</p>
            </div>

            <!-- Secure Payment -->
            <div class="text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium">Secure Payment</h3>
                <p class="mt-2 text-sm text-gray-500">100% secure payment</p>
            </div>

            <!-- 24/7 Support -->
            <div class="text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium">24/7 Support</h3>
                <p class="mt-2 text-sm text-gray-500">Dedicated support</p>
            </div>

            <!-- Money Back -->
            <div class="text-center">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium">Money Back</h3>
                <p class="mt-2 text-sm text-gray-500">30 days guarantee</p>
            </div>
        </div>
    </div>
</div>
@endsection