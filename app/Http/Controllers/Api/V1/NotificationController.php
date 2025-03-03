<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()
            ->when($request->read === 'true', fn($q) => $q->read())
            ->when($request->read === 'false', fn($q) => $q->unread())
            ->latest()
            ->paginate($request->per_page ?? 15);

        return $this->successResponse($notifications);
    }

    public function show(Notification $notification)
    {
        $this->authorize('view', $notification);
        return $this->successResponse($notification);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id'
        ]);

        Notification::whereIn('id', $request->ids)
            ->where('notifiable_id', auth()->id())
            ->update(['read_at' => now()]);

        return $this->successResponse(['message' => 'Notifications marked as read']);
    }

    public function markAsUnread(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id'
        ]);

        Notification::whereIn('id', $request->ids)
            ->where('notifiable_id', auth()->id())
            ->update(['read_at' => null]);

        return $this->successResponse(['message' => 'Notifications marked as unread']);
    }

    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);
        $notification->delete();
        return $this->successResponse(['message' => 'Notification deleted successfully']);
    }
} 