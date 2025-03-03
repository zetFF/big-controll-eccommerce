@extends('layouts.admin')

@section('title', isset($product) ? 'Edit Product' : 'Create Product')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">{{ isset($product) ? 'Edit Product' : 'Create Product' }}</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <form action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="p-6 space-y-6">
        @csrf
        @if(isset($product))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $product->name ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" 
                       name="slug" 
                       id="slug" 
                       value="{{ old('slug', $product->slug ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" 
                       name="price" 
                       id="price" 
                       step="0.01"
                       value="{{ old('price', $product->price ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       required>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sale_price" class="block text-sm font-medium text-gray-700">Sale Price</label>
                <input type="number" 
                       name="sale_price" 
                       id="sale_price" 
                       step="0.01"
                       value="{{ old('sale_price', $product->sale_price ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('sale_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                <input type="number" 
                       name="stock" 
                       id="stock" 
                       value="{{ old('stock', $product->stock ?? 0) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       required>
                @error('stock')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                <input type="text" 
                       name="sku" 
                       id="sku" 
                       value="{{ old('sku', $product->sku ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('sku')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" 
                      id="description" 
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $product->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Categories</label>
            <div class="mt-2 space-y-2">
                @foreach($categories as $category)
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="categories[]" 
                               value="{{ $category->id }}"
                               {{ in_array($category->id, old('categories', isset($product) ? $product->categories->pluck('id')->toArray() : [])) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-2 text-sm text-gray-600">{{ $category->name }}</label>
                    </div>
                @endforeach
            </div>
            @error('categories')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="images" class="block text-sm font-medium text-gray-700">Images</label>
            <input type="file" 
                   name="images[]" 
                   id="images"
                   multiple
                   accept="image/*"
                   class="mt-1 block w-full">
            @if(isset($product) && $product->images)
                <div class="mt-2 grid grid-cols-6 gap-2">
                    @foreach($product->images as $image)
                        <div class="relative">
                            <img src="{{ Storage::url($image) }}" alt="" class="h-24 w-24 object-cover rounded">
                            <input type="checkbox" 
                                   name="remove_images[]" 
                                   value="{{ $image }}"
                                   class="absolute top-0 right-0 m-1">
                        </div>
                    @endforeach
                </div>
                <p class="mt-1 text-sm text-gray-500">Check images to remove them</p>
            @endif
            @error('images')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center space-x-4">
            <label class="inline-flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Active</span>
            </label>

            <label class="inline-flex items-center">
                <input type="checkbox" 
                       name="is_featured" 
                       value="1"
                       {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Featured</span>
            </label>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.products.index') }}" 
               class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ isset($product) ? 'Update' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection 