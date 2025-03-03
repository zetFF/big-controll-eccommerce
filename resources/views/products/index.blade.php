@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Sidebar Filters -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="space-y-6">
                    <!-- Categories -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Categories</h3>
                        <div class="mt-2 space-y-2">
                            @foreach($categories as $category)
                                <div class="flex items-center">
                                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                                       class="text-sm text-gray-600 hover:text-gray-900">
                                        {{ $category->name }} ({{ $category->products_count }})
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Price Range</h3>
                        <div class="mt-2 space-y-2">
                            <div>
                                <label for="min_price" class="text-sm text-gray-600">Min Price</label>
                                <input type="number" 
                                       id="min_price" 
                                       name="min_price" 
                                       value="{{ request('min_price') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="max_price" class="text-sm text-gray-600">Max Price</label>
                                <input type="number" 
                                       id="max_price" 
                                       name="max_price" 
                                       value="{{ request('max_price') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="lg:col-span-9">
                <!-- Sort Options -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate">
                            Products
                        </h2>
                    </div>
                    <div>
                        <select id="sort" 
                                name="sort" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-12">
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter to find what you're looking for.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($products as $product)
                            <div class="group relative">
                                <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200">
                                    @if($product->images->isNotEmpty())
                                        <img src="{{ Storage::url($product->images->first()->image_path) }}" 
                                             alt="{{ $product->name }}"
                                             class="h-full w-full object-cover object-center">
                                    @endif
                                </div>
                                <div class="mt-4 flex justify-between">
                                    <div>
                                        <h3 class="text-sm text-gray-700">
                                            <a href="{{ route('products.show', $product) }}">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $product->category->name }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">${{ number_format($product->price, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('sort').addEventListener('change', function() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('sort', this.value);
    window.location.search = urlParams.toString();
});

const minPrice = document.getElementById('min_price');
const maxPrice = document.getElementById('max_price');
let timeout;

[minPrice, maxPrice].forEach(input => {
    input.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const urlParams = new URLSearchParams(window.location.search);
            if (minPrice.value) urlParams.set('min_price', minPrice.value);
            else urlParams.delete('min_price');
            if (maxPrice.value) urlParams.set('max_price', maxPrice.value);
            else urlParams.delete('max_price');
            window.location.search = urlParams.toString();
        }, 500);
    });
});
</script>
@endpush
@endsection 