<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryCacheService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CategoryCacheService $categoryCache
    ) {}

    public function index()
    {
        $categories = $this->categoryCache->getCategories();

        return $this->successResponse(
            CategoryResource::collection($categories)
        );
    }

    public function store(Request $request)
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);

        $this->categoryCache->clearCache();

        return $this->createdResponse(
            new CategoryResource($category)
        );
    }

    public function show(int $id)
    {
        $category = $this->categoryCache->getCategory($id);

        if (!$category) {
            return $this->errorResponse('Category not found', 404);
        }

        return $this->successResponse(
            new CategoryResource($category)
        );
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        $this->categoryCache->clearCache($category);

        return $this->successResponse(
            new CategoryResource($category)
        );
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        // Check if category has children
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with subcategories'
            ], 422);
        }

        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        $this->categoryCache->clearCache($category);

        return $this->noContentResponse();
    }

    public function reorder(Request $request)
    {
        $this->authorize('update', Category::class);

        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['categories'] as $categoryData) {
            Category::where('id', $categoryData['id'])
                ->update(['order' => $categoryData['order']]);
        }

        $this->categoryCache->clearCache();

        return response()->json([
            'message' => 'Categories reordered successfully'
        ]);
    }
} 