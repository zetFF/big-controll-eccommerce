<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with(['user', 'product'])
            ->when($request->product_id, function ($query, $productId) {
                return $query->where('product_id', $productId);
            })
            ->when(!auth()->user()->isAdmin(), function ($query) {
                return $query->where('is_approved', true);
            })
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        // Check if user has purchased the product
        $verifiedPurchase = Order::where('user_id', auth()->id())
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->where('status', 'delivered')
            ->exists();

        // Check if user has already reviewed this product
        $existingReview = Review::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product');
        }

        $review = new Review([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => $verifiedPurchase
        ]);

        $product->reviews()->save($review);

        return back()->with('success', 'Thank you for your review!');
    }

    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $review->update($validated);

        return back()->with('success', 'Your review has been updated');
    }

    public function approve(Review $review)
    {
        $this->authorize('approve', $review);

        $review->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Review approved successfully',
            'review' => $review
        ]);
    }

    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review has been deleted');
    }
} 