<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantType;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductVariantController extends Controller
{
    public function create(Product $product)
    {
        $variantTypes = VariantType::with('values')->get();
        return view('admin.products.variants.create', compact('product', 'variantTypes'));
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'variant_values' => 'required|array',
            'variant_values.*' => 'required|exists:variant_values,id',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $variant = $product->variants()->create([
            'sku' => $validated['sku'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'variant_values' => $validated['variant_values']
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/variants', 'public');
                $variant->images()->create([
                    'image_path' => $path,
                    'order' => $index
                ]);
            }
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product variant created successfully');
    }

    public function edit(Product $product, ProductVariant $variant)
    {
        $variantTypes = VariantType::with('values')->get();
        return view('admin.products.variants.edit', compact('product', 'variant', 'variantTypes'));
    }

    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:product_variants,sku,' . $variant->id,
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'variant_values' => 'required|array',
            'variant_values.*' => 'required|exists:variant_values,id',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $variant->update([
            'sku' => $validated['sku'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'variant_values' => $validated['variant_values']
        ]);

        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($variant->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            $variant->images()->delete();

            // Upload new images
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/variants', 'public');
                $variant->images()->create([
                    'image_path' => $path,
                    'order' => $index
                ]);
            }
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product variant updated successfully');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        foreach ($variant->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        $variant->delete();

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product variant deleted successfully');
    }
} 