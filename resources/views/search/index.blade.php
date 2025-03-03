@extends('layouts.app')

@section('title', 'Search Products')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Sidebar Filters -->
            <div class="hidden lg:block lg:col-span-3">
                <div class="space-y-6">
                    <!-- Search Form -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Search</h3>
                        <div class="mt-2">
                            <form action="{{ route('search') }}" method="GET">
                                <div class="flex rounded-md shadow-sm">
                                    <input type="text"
                                           name="q"
                                           value="{{ request('q') }}"
                                           class="flex-1 min-w-0 block w-full px-3 py-2 rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           placeholder="Search products...">
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Categories</h3>
                        <div class="mt-2 space-y-2">
                            @foreach($categories as $category)
                                <div class="flex items-center">
                                    <input type="checkbox"
                                           name="category[]"
                                           value="{{ $category->slug }}"
                                           {{ in_array($category->slug, (array)request('category')) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 text-sm text-gray-600">
                                        {{ $category->name }} ({{ $category->products_count }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Price Range</h3>
                        <div class="mt-2 space-y-4">
                            <div>
                                <label class="text-sm text-gray-600">Min Price</label>
                                <input type="number"
                                       name="min_price"
                                       value="{{ request('min_price') }}"
                                       min="{{ floor($priceRange->min_price) }}"
                                       max="{{ ceil($priceRange->max_price) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Max Price</label>
                                <input type="number"
                                       name="max_price"
                                       value="{{ request('max_price') }}"
                                       min="{{ floor($priceRange->min_price) }}"
                                       max="{{ ceil($priceRange->max_price) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Availability -->
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="in_stock"
                                   value="1"
                                   {{ request('in_stock') ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-600">
                                In Stock Only
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="mt-6 lg:mt-0 lg:col-span-9">
                <!-- Sort Options -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            {{ request('q') ? 'Search Results for "'.request('q').'"' : 'All Products' }}
                        </h2>
                    </div>
                    <div class="ml-4">
                        <select onchange="window.location.href=this.value"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}"
                                    {{ request('sort') == 'latest' ? 'selected' : '' }}>
                                Latest
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}"
                                    {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                Price: Low to High
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}"
                                    {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                Price: High to Low
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'name_asc']) }}"
                                    {{ request('sort') == 'name_asc' ? 'selected' : '' }}>
                                Name: A to Z
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}"
                                    {{ request('sort') == 'popular' ? 'selected' : '' }}>
                                Most Popular
                            </option>
                        </select>
                    </div>
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-12">
                        <h3 class="text-lg font-medium text-gray-900">No products found</h3>
                        <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
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
document.querySelectorAll('input[type="checkbox"], input[type="number"]').forEach(input => {
    input.addEventListener('change', () => {
        let form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route('search') }}';

        // Add all checked categories
        document.querySelectorAll('input[name="category[]"]:checked').forEach(checkbox => {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });

        // Add price range
        let minPrice = document.querySelector('input[name="min_price"]').value;
        let maxPrice = document.querySelector('input[name="max_price"]').value;
        if (minPrice) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'min_price';
            input.value = minPrice;
            form.appendChild(input);
        }
        if (maxPrice) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'max_price';
            input.value = maxPrice;
            form.appendChild(input);
        }

        // Add in_stock filter
        let inStock = document.querySelector('input[name="in_stock"]');
        if (inStock && inStock.checked) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'in_stock';
            input.value = '1';
            form.appendChild(input);
        }

        // Add search query if exists
        let searchQuery = new URLSearchParams(window.location.search).get('q');
        if (searchQuery) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'q';
            input.value = searchQuery;
            form.appendChild(input);
        }

        // Add sort if exists
        let sort = new URLSearchParams(window.location.search).get('sort');
        if (sort) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'sort';
            input.value = sort;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endpush
@endsection 