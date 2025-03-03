<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductRequest;
use App\Traits\ApiResponse;
use App\Services\ProductCacheService;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProductCacheService $productCache
    ) {}

    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get list of products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by product name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images']);

        // Filter by category if provided
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

        // Sort products
        switch ($request->get('sort', 'latest')) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->popular();
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * @OA\Post(
     *     path="/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProductRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validated();

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'sku' => $this->generateSku(),
        ]);

        $product->categories()->attach($validated['category_ids']);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image' => $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $this->productCache->clearCache($product);

        return $this->createdResponse(
            new ProductResource($product->load(['categories', 'images']))
        );
    }

    public function show(Product $product)
    {
        $product->load(['category', 'images', 'reviews.user']);
        
        if (auth()->check()) {
            auth()->user()->addToRecentlyViewed($product);
        }

        $relatedProducts = $product->getRelatedProducts();
        $recommendedProducts = $product->getRecommendedProducts();
        $recentlyViewed = auth()->check() 
            ? auth()->user()->recentlyViewed()->where('id', '!=', $product->id)->take(4)->get() 
            : collect();
        
        return view('products.show', compact(
            'product',
            'relatedProducts',
            'recommendedProducts',
            'recentlyViewed'
        ));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        if (isset($validated['category_ids'])) {
            $product->categories()->sync($validated['category_ids']);
        }

        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image);
            }
            $product->images()->delete();

            // Upload new images
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image' => $path,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        $this->productCache->clearCache($product);

        return $this->successResponse(
            new ProductResource($product->load(['categories', 'images']))
        );
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Delete product images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $product->delete();

        $this->productCache->clearCache($product);

        return $this->noContentResponse();
    }

    private function generateSku()
    {
        do {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        } while (Product::where('sku', $sku)->exists());

        return $sku;
    }
} 