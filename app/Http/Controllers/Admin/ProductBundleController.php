<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductBundleController extends Controller
{
    public function index()
    {
        $bundles = ProductBundle::with('products')->latest()->paginate(10);
        return view('admin.bundles.index', compact('bundles'));
    }

    public function create()
    {
        $products = Product::all();
        return view('admin.bundles.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        $bundle = ProductBundle::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'discount_amount' => $validated['discount_amount'],
            'is_active' => $validated['is_active'] ?? false
        ]);

        foreach ($validated['products'] as $product) {
            $bundle->products()->attach($product['id'], [
                'quantity' => $product['quantity']
            ]);
        }

        return redirect()
            ->route('admin.bundles.index')
            ->with('success', 'Product bundle created successfully');
    }

    public function edit(ProductBundle $bundle)
    {
        $products = Product::all();
        return view('admin.bundles.edit', compact('bundle', 'products'));
    }

    public function update(Request $request, ProductBundle $bundle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        $bundle->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'discount_amount' => $validated['discount_amount'],
            'is_active' => $validated['is_active'] ?? false
        ]);

        $bundle->products()->detach();
        
        foreach ($validated['products'] as $product) {
            $bundle->products()->attach($product['id'], [
                'quantity' => $product['quantity']
            ]);
        }

        return redirect()
            ->route('admin.bundles.index')
            ->with('success', 'Product bundle updated successfully');
    }

    public function destroy(ProductBundle $bundle)
    {
        $bundle->delete();

        return redirect()
            ->route('admin.bundles.index')
            ->with('success', 'Product bundle deleted successfully');
    }
} 