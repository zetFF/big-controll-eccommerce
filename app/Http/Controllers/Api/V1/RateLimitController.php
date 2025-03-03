<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RateLimit;
use Illuminate\Http\Request;

class RateLimitController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $limits = RateLimit::with(['creator', 'logs' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($limits);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'type' => 'required|in:' . implode(',', RateLimit::TYPES),
            'limit' => 'required|integer|min:1',
            'window' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $rateLimit = RateLimit::create(array_merge(
            $validated,
            ['created_by' => auth()->id()]
        ));

        return $this->successResponse($rateLimit, 201);
    }

    public function show(RateLimit $rateLimit)
    {
        return $this->successResponse(
            $rateLimit->load(['creator', 'logs' => function ($query) {
                $query->latest()->limit(10);
            }])
        );
    }

    public function update(Request $request, RateLimit $rateLimit)
    {
        $validated = $request->validate([
            'limit' => 'integer|min:1',
            'window' => 'integer|min:1',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $rateLimit->update($validated);

        return $this->successResponse($rateLimit);
    }

    public function destroy(RateLimit $rateLimit)
    {
        $rateLimit->delete();
        return $this->successResponse(['message' => 'Rate limit deleted successfully']);
    }

    public function logs(RateLimit $rateLimit)
    {
        $logs = $rateLimit->logs()
            ->latest()
            ->paginate(request()->per_page ?? 15);

        return $this->successResponse($logs);
    }
} 