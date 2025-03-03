<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        auth()->user()
            ->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read');
    }

    public function destroy(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        
        $notification->delete();

        return back()->with('success', 'Notification deleted');
    }
} 