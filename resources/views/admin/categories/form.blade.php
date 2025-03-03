@extends('layouts.admin')

@section('title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="p-6 space-y-6">
        @csrf
        @if(isset($category))
            @method('PUT')
        @endif

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" 
                   name="name" 
                   id="name" 
                   value="{{ old('name', $category->name ?? '') }}"
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
                   value="{{ old('slug', $category->slug ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" 
                      id="description" 
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $category->description ?? '') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
            <input type="file" 
                   name="image" 
                   id="image"
                   accept="image/*"
                   class="mt-1 block w-full">
            @if(isset($category) && $category->image)
                <div class="mt-2">
                    <img src="{{ Storage::url($category->image) }}" alt="" class="h-32 w-32 object-cover rounded">
                </div>
            @endif
            @error('image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
            <input type="number" 
                   name="sort_order" 
                   id="sort_order" 
                   value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('sort_order')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-600">Active</span>
            </label>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.categories.index') }}" 
               class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ isset($category) ? 'Update' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection 