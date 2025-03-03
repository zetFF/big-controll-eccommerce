@extends('layouts.admin')

@section('title', isset($bundle) ? 'Edit Bundle' : 'Create Bundle')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold">
                {{ isset($bundle) ? 'Edit Bundle' : 'Create Bundle' }}
            </h1>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ isset($bundle) ? route('admin.bundles.update', $bundle) : route('admin.bundles.store') }}"
                  method="POST">
                @csrf
                @if(isset($bundle))
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Bundle Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $bundle->name ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               required>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description"
                                  id="description"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description', $bundle->description ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Bundle Price</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number"
                                       name="price"
                                       id="price"
                                       step="0.01"
                                       value="{{ old('price', $bundle->price ?? '') }}"
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                       required>
                            </div>
                        </div>

                        <div>
                            <label for="discount_amount" class="block text-sm font-medium text-gray-700">Discount Amount</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number"
                                       name="discount_amount"
                                       id="discount_amount"
                                       step="0.01"
                                       value="{{ old('discount_amount', $bundle->discount_amount ?? 0) }}"
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                       required>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $bundle->is_active ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active
                            </label>
                        </div>
                    </div>

                    <!-- Products -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bundle Products</label>
                        <div id="products-container" class="space-y-4">
                            @if(isset($bundle))
                                @foreach($bundle->products as $bundleProduct)
                                    <div class="product-row flex items-center space-x-4">
                                        <div class="flex-1">
                                            <select name="products[{{ $loop->index }}][id]"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                    required>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        {{ $bundleProduct->id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (${{ number_format($product->price, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-32">
                                            <input type="number"
                                                   name="products[{{ $loop->index }}][quantity]"
                                                   value="{{ $bundleProduct->pivot->quantity }}"
                                                   min="1"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                   required>
                                        </div>
                                        <button type="button"
                                                onclick="removeProduct(this)"
                                                class="text-red-600 hover:text-red-900">
                                            Remove
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <button type="button"
                                onclick="addProduct()"
                                class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Add Product
                        </button>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.bundles.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            {{ isset($bundle) ? 'Update Bundle' : 'Create Bundle' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addProduct() {
    const container = document.getElementById('products-container');
    const index = container.children.length;
    const template = `
        <div class="product-row flex items-center space-x-4">
            <div class="flex-1">
                <select name="products[${index}][id]"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (${{ number_format($product->price, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-32">
                <input type="number"
                       name="products[${index}][quantity]"
                       value="1"
                       min="1"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                       required>
            </div>
            <button type="button"
                    onclick="removeProduct(this)"
                    class="text-red-600 hover:text-red-900">
                Remove
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', template);
}

function removeProduct(button) {
    button.closest('.product-row').remove();
    reindexProducts();
}

function reindexProducts() {
    const rows = document.querySelectorAll('.product-row');
    rows.forEach((row, index) => {
        row.querySelector('select').name = `products[${index}][id]`;
        row.querySelector('input').name = `products[${index}][quantity]`;
    });
}

// Add at least one product row if creating new bundle
@if(!isset($bundle))
    addProduct();
@endif
</script>
@endpush
@endsection 