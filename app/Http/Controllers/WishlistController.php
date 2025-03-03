<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = auth()->user()
            ->wishlist()
            ->with(['category', 'images'])
            ->latest('wishlists.created_at')
            ->paginate(12);

        return view('wishlist.index', compact('wishlist'));
    }

    public function store(Request $request, Product $product)
    {
        if (!auth()->user()->hasInWishlist($product)) {
            auth()->user()->wishlist()->attach($product);
            $message = 'Product added to wishlist';
        } else {
            auth()->user()->wishlist()->detach($product);
            $message = 'Product removed from wishlist';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'in_wishlist' => auth()->user()->hasInWishlist($product)
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Product $product)
    {
        auth()->user()->wishlist()->detach($product);

        return back()->with('success', 'Product removed from wishlist');
    }
} 