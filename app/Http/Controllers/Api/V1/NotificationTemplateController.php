<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth:sanctum', 'admin']);
    }

    public function index(Request $request)
    {
        $templates = NotificationTemplate::with('creator')
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:notification_templates',
            'type' => 'required|in:' . implode(',', NotificationTemplate::TYPES),
            'channels' => 'required|array',
            'channels.*' => 'in:email,sms,push,database',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'metadata' => 'nullable|array',
        ]);

        $template = NotificationTemplate::create(array_merge(
            $validated,
            ['created_by' => auth()->id()]
        ));

        return $this->successResponse($template, 201);
    }

    public function show(NotificationTemplate $template)
    {
        return $this->successResponse($template->load('creator'));
    }

    public function update(Request $request, NotificationTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'string|max:255|unique:notification_templates,name,' . $template->id,
            'type' => 'in:' . implode(',', NotificationTemplate::TYPES),
            'channels' => 'array',
            'channels.*' => 'in:email,sms,push,database',
            'subject' => 'string|max:255',
            'content' => 'string',
            'metadata' => 'nullable|array',
        ]);

        $template->update($validated);

        return $this->successResponse($template);
    }

    public function destroy(NotificationTemplate $template)
    {
        $template->delete();
        return $this->successResponse(['message' => 'Template deleted successfully']);
    }
} 