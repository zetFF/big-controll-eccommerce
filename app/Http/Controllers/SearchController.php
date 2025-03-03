<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'images']);

        // Search by keyword
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%')
                  ->orWhereHas('category', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->q . '%');
                  });
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by availability
        if ($request->filled('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sort products
        $sortField = 'created_at';
        $sortDirection = 'desc';

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $sortField = 'price';
                    $sortDirection = 'asc';
                    break;
                case 'price_desc':
                    $sortField = 'price';
                    $sortDirection = 'desc';
                    break;
                case 'name_asc':
                    $sortField = 'name';
                    $sortDirection = 'asc';
                    break;
                case 'popular':
                    $query->popular();
                    break;
                default:
                    break;
            }
        }

        if ($request->sort !== 'popular') {
            $query->orderBy($sortField, $sortDirection);
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->get();

        // Get price range for filters
        $priceRange = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        return view('search.index', compact('products', 'categories', 'priceRange'));
    }
} 