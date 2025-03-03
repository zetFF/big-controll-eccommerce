<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')
            ->latest()
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images.*' => 'image|max:2048',
            'categories' => 'required|array|exists:categories,id',
            'attributes' => 'nullable|array'
        ]);

        if ($request->hasFile('images')) {
            $validated['images'] = collect($request->file('images'))
                ->map(fn($file) => $file->store('products', 'public'))
                ->toArray();
        }

        $product = Product::create($validated);
        $product->categories()->sync($request->categories);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'images.*' => 'image|max:2048',
            'categories' => 'required|array|exists:categories,id',
            'attributes' => 'nullable|array',
            'remove_images' => 'nullable|array'
        ]);

        // Handle image removal
        if ($request->remove_images) {
            $remainingImages = collect($product->images)
                ->reject(fn($image) => in_array($image, $request->remove_images))
                ->values()
                ->toArray();
            
            foreach ($request->remove_images as $image) {
                Storage::disk('public')->delete($image);
            }
            
            $validated['images'] = $remainingImages;
        }

        // Handle new images
        if ($request->hasFile('images')) {
            $newImages = collect($request->file('images'))
                ->map(fn($file) => $file->store('products', 'public'))
                ->toArray();

            $validated['images'] = array_merge($validated['images'] ?? $product->images ?? [], $newImages);
        }

        $product->update($validated);
        $product->categories()->sync($request->categories);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }
} 