@extends('layouts.admin')

@section('title', isset($variant) ? 'Edit Variant' : 'Create Variant')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold">
                {{ isset($variant) ? 'Edit Variant' : 'Create Variant' }} for {{ $product->name }}
            </h1>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ isset($variant) 
                ? route('admin.products.variants.update', [$product, $variant]) 
                : route('admin.products.variants.store', $product) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @if(isset($variant))
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <!-- SKU -->
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                        <input type="text"
                               name="sku"
                               id="sku"
                               value="{{ old('sku', $variant->sku ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               required>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number"
                                   name="price"
                                   id="price"
                                   step="0.01"
                                   value="{{ old('price', $variant->price ?? '') }}"
                                   class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   required>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number"
                               name="stock"
                               id="stock"
                               value="{{ old('stock', $variant->stock ?? 0) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               required>
                    </div>

                    <!-- Variant Values -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Variant Options</label>
                        @foreach($variantTypes as $type)
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">{{ $type->name }}</label>
                                <select name="variant_values[]"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        required>
                                    <option value="">Select {{ $type->name }}</option>
                                    @foreach($type->values as $value)
                                        <option value="{{ $value->id }}"
                                            {{ isset($variant) && in_array($value->id, $variant->variant_values->pluck('id')->toArray()) 
                                                ? 'selected' : '' }}>
                                            {{ $value->value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <!-- Images -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Images</label>
                        <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload files</span>
                                        <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 2MB</p>
                            </div>
                        </div>
                    </div>

                    @if(isset($variant) && $variant->images->isNotEmpty())
                        <div class="grid grid-cols-4 gap-4">
                            @foreach($variant->images as $image)
                                <div class="relative">
                                    <img src="{{ Storage::url($image->image_path) }}" 
                                         alt="Variant image" 
                                         class="w-full h-32 object-cover rounded">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.products.edit', $product) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            {{ isset($variant) ? 'Update Variant' : 'Create Variant' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 