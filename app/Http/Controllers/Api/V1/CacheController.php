<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cache;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CacheController extends BaseController
{
    public function __construct(
        private CacheService $cacheService
    ) {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $caches = Cache::when($request->search, function($q) use ($request) {
                $q->where('key', 'LIKE', "%{$request->search}%")
                    ->orWhereJsonContains('tags', $request->search);
            })
            ->when($request->status === 'active', fn($q) => $q->active())
            ->when($request->status === 'expired', fn($q) => $q->expired())
            ->when($request->tags, fn($q) => $q->withTags(explode(',', $request->tags)))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($caches);
    }

    public function show(string $key)
    {
        $cache = Cache::where('key', $key)->firstOrFail();
        return $this->successResponse($cache);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
            'expiration' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'metadata' => 'nullable|array'
        ]);

        $cache = $this->cacheService->set(
            $validated['key'],
            $validated['value'],
            $validated['expiration'] ? Carbon::parse($validated['expiration']) : null,
            $validated['tags'] ?? [],
            $validated['metadata'] ?? []
        );

        return $this->successResponse($cache, 201);
    }

    public function destroy(string $key)
    {
        if ($this->cacheService->forget($key)) {
            return $this->successResponse(['message' => 'Cache entry deleted successfully']);
        }

        return $this->errorResponse('Cache entry not found', 404);
    }

    public function flush(Request $request)
    {
        $count = $this->cacheService->flush(
            $request->tags ? explode(',', $request->tags) : []
        );

        return $this->successResponse([
            'message' => "Successfully flushed {$count} cache entries"
        ]);
    }

    public function cleanup()
    {
        $count = $this->cacheService->cleanup();

        return $this->successResponse([
            'message' => "Successfully cleaned up {$count} expired cache entries"
        ]);
    }

    public function stats()
    {
        return $this->successResponse(
            $this->cacheService->stats()
        );
    }
} 