<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $webhooks = Webhook::with(['creator', 'deliveries' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($webhooks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array',
            'events.*' => 'string',
            'headers' => 'nullable|array',
            'retry_count' => 'integer|min:0|max:10',
            'timeout' => 'integer|min:1|max:30',
            'metadata' => 'nullable|array',
        ]);

        $webhook = Webhook::create(array_merge(
            $validated,
            [
                'secret' => Str::random(32),
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        ));

        return $this->successResponse($webhook, 201);
    }

    public function show(Webhook $webhook)
    {
        return $this->successResponse(
            $webhook->load(['creator', 'deliveries' => function ($query) {
                $query->latest()->limit(10);
            }])
        );
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'url' => 'url',
            'events' => 'array',
            'events.*' => 'string',
            'is_active' => 'boolean',
            'headers' => 'nullable|array',
            'retry_count' => 'integer|min:0|max:10',
            'timeout' => 'integer|min:1|max:30',
            'metadata' => 'nullable|array',
        ]);

        $webhook->update($validated);

        return $this->successResponse($webhook);
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();
        return $this->successResponse(['message' => 'Webhook deleted successfully']);
    }

    public function regenerateSecret(Webhook $webhook)
    {
        $webhook->update(['secret' => Str::random(32)]);
        return $this->successResponse($webhook);
    }

    public function deliveries(Webhook $webhook)
    {
        $deliveries = $webhook->deliveries()
            ->latest()
            ->paginate(request()->per_page ?? 15);

        return $this->successResponse($deliveries);
    }
} 