<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $popularProducts = Product::popular()
            ->with(['category', 'images'])
            ->take(8)
            ->get();

        $newArrivals = Product::with(['category', 'images'])
            ->latest()
            ->take(8)
            ->get();

        $featuredCategories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->take(6)
            ->get();

        return view('home', compact(
            'popularProducts',
            'newArrivals',
            'featuredCategories'
        ));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function terms()
    {
        return view('terms');
    }
} 