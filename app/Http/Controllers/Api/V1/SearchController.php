<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    public function __construct(
        private SearchService $searchService
    ) {
        parent::__construct();
        $this->middleware('auth:sanctum');
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'nullable|string',
            'types' => 'nullable|array',
            'types.*' => 'string',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $results = $this->searchService->search(
            $request->query,
            [
                'type' => $request->type,
                'types' => $request->types
            ],
            [
                'per_page' => $request->per_page,
                'page' => $request->page
            ]
        );

        return $this->successResponse($results);
    }

    public function suggest(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:10'
        ]);

        $suggestions = $this->searchService->suggest(
            $request->query,
            $request->limit ?? 5
        );

        return $this->successResponse($suggestions);
    }

    public function reindex(Request $request)
    {
        $this->middleware('admin');
        $this->searchService->reindex();
        return $this->successResponse(['message' => 'Reindexing completed successfully']);
    }

    private function searchProducts(Request $request): array
    {
        $results = Product::search(
            $request->query,
            $request->filters ?? []
        );

        return [
            'type' => 'products',
            'results' => $results
        ];
    }

    private function searchOrders(Request $request): array
    {
        $results = Order::search(
            $request->query,
            $request->filters ?? []
        );

        return [
            'type' => 'orders',
            'results' => $results
        ];
    }

    private function searchUsers(Request $request): array
    {
        $results = User::search(
            $request->query,
            $request->filters ?? []
        );

        return [
            'type' => 'users',
            'results' => $results
        ];
    }

    private function searchAll(Request $request): array
    {
        return [
            'products' => $this->searchProducts($request)['results'],
            'orders' => $this->searchOrders($request)['results'],
            'users' => $this->searchUsers($request)['results']
        ];
    }
} 