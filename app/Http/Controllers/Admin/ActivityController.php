<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with('user')
            ->when(request('type'), function($q) {
                $q->where('type', request('type'));
            })
            ->when(request('user'), function($q) {
                $q->where('user_id', request('user'));
            })
            ->when(request('action'), function($q) {
                $q->where('action', request('action'));
            })
            ->when(request('date'), function($q) {
                $q->whereDate('created_at', request('date'));
            })
            ->latest()
            ->paginate(50);

        return view('admin.activities.index', compact('activities'));
    }

    public function show(Activity $activity)
    {
        return view('admin.activities.show', compact('activity'));
    }

    public function clear()
    {
        $days = request('days', 30);
        Activity::where('created_at', '<', now()->subDays($days))->delete();

        return back()->with('success', "Activities older than {$days} days have been cleared.");
    }
} 