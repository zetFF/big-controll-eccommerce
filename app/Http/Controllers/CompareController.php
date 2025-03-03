<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $productIds = $request->session()->get('compare', []);
        $products = Product::whereIn('id', $productIds)->get();

        return view('compare.index', compact('products'));
    }

    public function add(Request $request, Product $product)
    {
        $compareIds = $request->session()->get('compare', []);

        // Limit to 4 products
        if (count($compareIds) >= 4) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'You can compare up to 4 products at a time'
                ], 422);
            }
            return back()->with('error', 'You can compare up to 4 products at a time');
        }

        if (!in_array($product->id, $compareIds)) {
            $compareIds[] = $product->id;
            $request->session()->put('compare', $compareIds);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Product added to comparison',
                'count' => count($compareIds)
            ]);
        }

        return back()->with('success', 'Product added to comparison');
    }

    public function remove(Request $request, Product $product)
    {
        $compareIds = $request->session()->get('compare', []);
        $compareIds = array_diff($compareIds, [$product->id]);
        $request->session()->put('compare', $compareIds);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Product removed from comparison',
                'count' => count($compareIds)
            ]);
        }

        return back()->with('success', 'Product removed from comparison');
    }

    public function clear(Request $request)
    {
        $request->session()->forget('compare');

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Comparison list cleared']);
        }

        return back()->with('success', 'Comparison list cleared');
    }
} 